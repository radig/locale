<?php
/**
 * Behavior to automagic convert dates, numbers and currency from
 * any localized format to DB format for security store.
 *
 * Code comments in brazilian portuguese.
 * -----
 * Behavior para converter automagicamente datas, números decimais e valores
 * monetários de qualquer formato localizado para o formato aceito pelo BD
 * em uso.
 *
 * PHP version > 5.3
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2012, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Radig.Locale
 * @subpackage Radig.Locale.Model.Behavior
 */
class LocaleBehavior extends ModelBehavior
{
	/**
	 * Modelo corrente
	 *
	 * @var Model
	 */
	private $_model;

	/**
	 * Lista de campos com o seu respectivo tipo para o modelo em uso
	 *
	 * @var array
	 */
	private $_modelFields;

	/**
	 * Lista de campos que devem ser ignorados por serem inseridos
	 * automagicamente pelo CakePHP
	 *
	 * @var array
	 */
	private $cakeAutomagicFields = array('created', 'updated', 'modified');

	/**
	 * Lista de formatos para os dados suportados pelo BD em uso.
	 * É recuperado automáticamente pela conexão com o banco.
	 *
	 * @var array
	 */
	private $typesFormat;

	/**
	 * Cópia do valor da configuração 'Language.default' armazenada pela classe
	 * Configure.
	 *
	 * @var string
	 */
	private $systemLang;

	private $_defaultSettings = array(
		'ignoreAutomagic' => true
	);

	/**
	 * Inicializa os dados do behavior
	 *
	 * @see ModelBehavior::setup()
	 */
	public function setup(Model &$model, $config = array())
	{
		$this->systemLang = Configure::read('Language.default');

		if($this->systemLang === null)
			throw new ConfigureException("Você precisa definir a Configuração 'Langhage.default' para usar o Behavior Locale");

		$this->__checkConfig($model, $config);
	}

	/**
	 * Verifica e seta configurações pendentes
	 *
	 * @param  Model  $model
	 * @param  array  $config
	 * @return void
	 */
	private function __checkConfig(Model $model, $config = array())
	{
		if(empty($config))
			$this->settings[$model->alias] = Set::merge($this->_defaultSettings, $config);

		if(!isset($this->_modelFields[$model->alias]))
			$this->_modelFields[$model->alias] = @$model->getColumnTypes();

		if(isset($this->typesFormat[$model->useDbConfig]))
			return;

		$db =& $model->getDataSource();
		$this->typesFormat[$model->useDbConfig] = array();

		foreach($db->columns as $type => $info)
		{
			if(isset($info['format']))
				$this->typesFormat[$model->useDbConfig][$type] = $info['format'];
		}
	}

	/**
	 * Invoca localização das informações no callback beforeValidate
	 *
	 * @see ModelBehavior::beforeValidate()
	 */
	public function beforeValidate(Model &$model)
	{
		parent::beforeValidate($model);
		$this->_model =& $model;

		$this->__checkConfig($model);

		return $this->localizeData();
	}

	/**
	 * Invoca localização das informaçõs no callback beforeSave
	 *
	 * @see ModelBehavior::beforeSave()
	 */
	public function beforeSave(Model &$model)
	{
		parent::beforeSave($model);
		$this->_model =& $model;

		$this->__checkConfig($model);

		return $this->localizeData();
	}

	/**
	 * Invoca localização das informações no callback beforeFind
	 *
	 * @see ModelBehavior::beforeFind()
	 */
	public function beforeFind(&$model, $query)
	{
		parent::beforeFind($model, $query);
		$this->_model =& $model;

		$this->__checkConfig($model);

		$this->localizeData($query['conditions']);

		return $query;
	}

	/**
	 * Faz a localização das informações, convertendo-as de um formato
	 * arbitrário (localizado para o usuário) para o formato aceito pelo
	 * DB em uso.
	 *
	 * @param array $query utilizado no caso do callback beforeFind.
	 * Valor é passado por referência e é alterado no método.
	 *
	 * @return bool $status caso não haja falha retorna true, false caso contrário
	 */
	public function localizeData(&$query = null)
	{
		$status = true;

		// verifica se há dados setados no modelo
		if(isset($this->_model->data) && !empty($this->_model->data))
		{
			// varre os dados setados
			foreach($this->_model->data[$this->_model->alias] as $field => $value)
			{
				// caso o campo esteja vazio E não tenha um array como valor E o campo faz parte do schema
				if($this->__isLocalizableField($this->_model, $field))
				{
					switch($this->_modelFields[$this->_model->alias][$field])
					{
						case 'date':
						case 'datetime':
						case 'time':
						case 'timestamp':
							$status = ($status && $this->__dateConvert($this->_model->data[$this->_model->alias][$field], $this->_modelFields[$this->_model->alias][$field]));
							break;
						case 'number':
						case 'decimal':
						case 'float':
						case 'double':
							$status = ($status && $this->__stringToFloat($this->_model->data[$this->_model->alias][$field]));
							break;
					}
				}
			}
		}

		// caso tenha sido invocado em um Find (haja query de busca)
		if(!empty($query) && is_array($query))
		{
			// varre os campos da condição
			foreach($query as $field => &$value)
			{
				if(strtolower($field) === 'or' || strtolower($field) === 'and' || is_numeric($field))
				{
					$status = $status && $this->localizeData($value);
					continue;
				}

				// caso sejam campos com a notação Model.field
				if(strpos($field, '.') !== false)
				{
					$ini = strpos($field, '.');
					$len = strpos($field, ' ');

					$modelName = substr($field, 0, $ini - 1);

					if($len !== false)
						$field = substr($field, $ini + 1, $len - $ini - 1);
					else
						$field = substr($field, $ini + 1);
				}

				if($this->__isLocalizableField($this->_model, $field, $value))
				{
					switch($this->_modelFields[$this->_model->alias][$field])
					{
						case 'date':
						case 'datetime':
						case 'time':
						case 'timestamp':
							if(is_array($value))
								foreach($value as &$v)
									$status = ($status && $this->__dateConvert($v, $this->_modelFields[$this->_model->alias][$field]));
							else
								$status = ($status && $this->__dateConvert($value, $this->_modelFields[$this->_model->alias][$field]));
							break;
						case 'decimal':
						case 'float':
						case 'double':
							if(is_array($value))
								foreach($value as &$v)
									$status = ($status && $this->__stringToFloat($v));
							else
								$status = ($status && $this->__stringToFloat($value));
							break;
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Converte uma string para um decimal localizado
	 *
	 * @param string $value
	 * @return bool
	 */
	private function __decimalConvert(&$value)
	{
		//TODO implementar um método específico para conversão de decimais, sem depender de extensão
	}

	/**
	 * Converte uma data localizada para padrão de banco de dados (americano)
	 *
	 * @param string $value
	 * @param string $type -> a valid schema date type, like: 'date', 'datetime', 'timestamp' or 'time'
	 * @return bool
	 */
	private function __dateConvert(&$value, $type = 'date')
	{
		// caso a data seja nula, não efetua conversão
		if(empty($value) || strpos('0000-00-00', $value) !== false)
		{
			return true;
		}

		if($this->systemLang == 'pt-br')
		{
			/*
			 * @FIXME remover redundância de busca de padrão
			 *
			 * Identifica padrão de data (pt-br) e converte para padrão en_US
			 */
			if( preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}/', $value) )
			{
				$value = preg_replace('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/', '$3-$2-$1', $value);
			}
			else if( preg_match('/^\d{1,2}\-\d{1,2}\-\d{2,4}/', $value) )
			{
				$value = preg_replace('/^(\d{1,2})\-(\d{1,2})\-(\d{2,4})/', "$3-$2-$1", $value);
			}

			/*
			 * Caso não tenha sido possível converter o formato, retorna false
			 */
			if($value == null)
			{
				return false;
			}
		}

		try {
			$dt = new DateTime($value);
		}
		catch(Exception $e)
		{
			return false;
		}

		$value = $dt->format($this->typesFormat[$this->_model->useDbConfig][$type]);

		return ($value !== false);
	}

	/**
	 * Converte uma string que representa um número em um float válido
	 *
	 * Ex.:
	 *  '1.000.000,22' vira '1000000.22'
	 *  '1.12' continua '1.12'
	 *  '1,12' vira '1.12'
	 *
	 * @param string $value
	 * @return bool
	 */
	private function __stringToFloat(&$value)
	{
		$isValid = false;

		// guarda o locale atual para restauração posterior
		$curLocale = setlocale(LC_NUMERIC, "0");

		// garante que o separador de decimal será o ponto (dot)
		setlocale(LC_NUMERIC, 'en_US');

		if(!empty($value))
		{
			// busca casas decimais
			if(preg_match('/([\.|,])([0-9]*)$/', $value, $d))
			{
				$d = $d[2];
			}
			else
			{
				// caso contrário, seta casas decimais com valor zero, por conveniência utilizando duas casas
				$d = '00';
			}

			// recupera os digitos "inteiros"
			$arrTmp = preg_split('/([\.|,])([0-9]*)$/', $value);
			$i = preg_replace('/[\.|,]/', '', $arrTmp[0]);

			// monta o número final
			$value = ($i . '.' . $d);

			$isValid = !empty($value);
		}
		else
		{
			$value = 0;
		}

		setlocale(LC_NUMERIC, $curLocale);

		return $isValid;
	}

	/**
	 * Verifica se um determinado campo de um modelo é
	 * localizável.
	 *
	 * @param  Model $model
	 * @param  string $field
	 * @return bool
	 */
	private function __isLocalizableField($model, $field, $value = null)
	{
		// caso não haja configuração do behavior para o modelo
		if(!isset($this->settings[$model->alias]))
			return false;

		// caso esteja vazio e setado via modelo
		if($value === null && isset($model->data[$model->alias][$field]) && empty($model->data[$model->alias][$field]))
			return false;

		// caso esteja vazio e setado via query
		if($value !== null && empty($value))
			return false;

		// caso não faça parte do schema
		if(!isset($this->_modelFields[$model->alias][$field]))
			return false;

		// caso seja um campo automágico, e estes seja ignorados
		if($this->settings[$model->alias]['ignoreAutomagic'] && in_array($field, $this->cakeAutomagicFields))
			return false;

		// caso contrário é sim localizável
		return true;
	}
}
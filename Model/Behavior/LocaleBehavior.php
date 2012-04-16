<?php
App::uses('Unlocalize', 'Locale.Lib');

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
		$this->settings = array(
			'ignoreAutomagic' => true
		);

		$this->model =& $model;
		$this->settings = Set::merge($this->settings, $config);

		$this->systemLang = substr(setlocale(LC_ALL, "0"), 0, 5);

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
	 * Converte uma data localizada para padrão de banco de dados (americano)
	 *
	 * @param string $value
	 * @param string $type -> a valid schema date type, like: 'date', 'datetime' or 'timestamp'
	 * @return bool
	 */
	private function __dateConvert(&$value, $type = 'date')
	{
		// both have same string format
		if($type == 'datetime')
			$type = 'timestamp';

		try
		{
			$d = Unlocalize::setLocale($this->systemLang)->date($value);
			$dt = new DateTime($d);
			$value = $dt->format($this->typesFormat[$this->_model->useDbConfig][$type]);
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
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
		$value = Unlocalize::setLocale($this->systemLang)->decimal($value);

		return true;
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
		if(!isset($this->settings[$model->alias]))
			return false;

		if($value === null && isset($model->data[$model->alias][$field]) && empty($model->data[$model->alias][$field]))
			return false;

		if($value !== null && empty($value))
			return false;

		if(!isset($this->_modelFields[$model->alias][$field]))
			return false;

		if($this->settings[$model->alias]['ignoreAutomagic'] && in_array($field, $this->cakeAutomagicFields))
			return false;

		return true;
	}
}
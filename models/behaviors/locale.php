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
 * PHP version > 5.2.4
 * 
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @copyright 2009-2011, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package radig
 * @subpackage radig.l10n.models.behaviors
 */

App::import('CORE', 'ConnectionManager');

class LocaleBehavior extends ModelBehavior
{
	/**
	 * Referência para o modelo que está utilizando o behavior
	 * @var Model
	 */
	protected $model;
	
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

	/**
	 * Inicializa os dados do behavior
	 * 
	 * @see ModelBehavior::setup()
	 */
	public function setup(&$model, $config = array())
	{
		$this->settings = array(
			'ignoreAutomagic' => true
		);
		
		$this->model =& $model;
		$this->settings = Set::merge($this->settings, $config);
		
		$this->systemLang = Configure::read('Language.default');
		
		$db =& ConnectionManager::getDataSource($this->model->useDbConfig);
		
		foreach($db->columns as $type => $info)
		{
			if(isset($info['format']))
			{
				$this->typesFormat[$type] = $info['format'];
			}
		}
	}

	/**
	 * Invoca localização das informações no callback beforeValidate
	 * 
	 * @see ModelBehavior::beforeValidate()
	 */
	public function beforeValidate(&$model)
	{
		$this->model =& $model;

		parent::beforeValidate($model);
		
		return $this->localizeData();
	}
	
	/**
	 * Invoca localização das informaçõs no callback beforeSave
	 * 
	 * @see ModelBehavior::beforeSave()
	 */
	public function beforeSave(&$model)
	{
		$this->model =& $model;

		parent::beforeSave($model);
		
		return $this->localizeData();
	}
	
	/**
	 * Invoca localização das informações no callback beforeFind
	 * 
	 * @see ModelBehavior::beforeFind()
	 */
	public function beforeFind(&$model, $query)
	{
		$this->model =& $model;
		
		parent::beforeFind($mode, $query);
		
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
		if(isset($this->model->data) && !empty($this->model->data))
		{
			// varre os dados setados
			foreach($this->model->data[$this->model->name] as $field => $value)
			{
				// caso o campo esteja vazio E não tenha um array como valor E o campo faz parte do schema
				if(!empty($value) && !is_array($value) && isset($this->model->_schema[$field]) && (!$this->settings['ignoreAutomagic'] || ($this->settings['ignoreAutomagic'] && !in_array($field, $this->cakeAutomagicFields))))
				{
					switch($this->model->_schema[$field]['type'])
					{
						case 'date':
						case 'datetime':
						case 'time':
						case 'timestamp':
							$status = ($status && $this->__dateConvert($this->model->data[$this->model->name][$field], $this->model->_schema[$field]['type']));
							break;
						case 'number':
						case 'decimal':
						case 'float':
						case 'double':
							$status = ($status && $this->__stringToFloat($this->model->data[$this->model->name][$field]));
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
				if(strtolower($field) === 'or' || strtolower($field) === 'and')
				{
					$status = $status && $this->localizeData($value);
					
					return $status;
				}
				
				if(strpos($field, '.') !== false)
				{
					$pos = strpos($field, '.');
					$len = strpos($field, ' ');
					$field = substr($field, $pos + 1, $len - $pos);
				}
				
				// caso o campo esteja vazio E não tenha um array como valor E o campo faz parte do schema
				if(!empty($value) && !is_array($value) && isset($this->model->_schema[$field]) && (!$this->settings['ignoreAutomagic'] || ($this->settings['ignoreAutomagic'] && !in_array($field, $this->cakeAutomagicFields))))
				{
					switch($this->model->_schema[$field]['type'])
					{
						case 'date':
						case 'datetime':
						case 'time':
						case 'timestamp':
							$status = ($status && $this->__dateConvert($value, $this->model->_schema[$field]['type']));
							break;
						case 'decimal':
						case 'float':
						case 'double':
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
			if( $value == null )
				return false;
		}

		try {
			$dt = new DateTime($value);
		}
		catch(Exception $e)
		{
			trigger_error(sprintf(__('Não foi possível converter a data %s no Behavior Locale', true), $value), E_USER_WARNING);

			return false;
		}
		$value = $dt->format($this->typesFormat[$type]);
		
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

		setlocale(LC_NUMERIC, $curLocale);
		
		return $isValid;
	}
}
?>
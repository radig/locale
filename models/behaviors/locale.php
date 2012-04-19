<?php
App::import('CORE', 'ConnectionManager');
App::import('Lib', 'Locale.Unlocalize');

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
 * @copyright 2009-2012, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package radig
 * @subpackage radig.l10n.models.behaviors
 */
class LocaleBehavior extends ModelBehavior
{
	/**
	 * Referência para o modelo que está utilizando o behavior
	 * @var Model
	 */
	protected $_Model;

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
	public function setup(&$model, $config = array())
	{
		$this->_Model =& $model;

		$this->systemLang = substr(setlocale(LC_ALL, "0"), 0, 5);

		$this->__checkConfig($model, $config);
	}

	/**
	 * Check and set missing configurations
	 *
	 * @param Model $model
	 * @param array $config
	 * @return void
	 */
	private function __checkConfig(Model $model, $config = null)
	{
		if($config !== null)
			$this->settings[$model->alias] = Set::merge($this->_defaultSettings, $config);

		if(!isset($this->_modelFields[$model->alias]))
			$this->_modelFields[$model->alias] = $model->getColumnTypes();

		if(isset($this->typesFormat[$model->useDbConfig]))
			return;

		$db =& ConnectionManager::getDataSource($model->useDbConfig);
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
	public function beforeValidate(&$model)
	{
		parent::beforeValidate($model);
		$this->_Model =& $model;

		$this->__checkConfig($model);

		return $this->localizeData();
	}

	/**
	 * Invoca localização das informaçõs no callback beforeSave
	 *
	 * @see ModelBehavior::beforeSave()
	 */
	public function beforeSave(&$model)
	{
		parent::beforeSave($model);
		$this->_Model =& $model;

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
		parent::beforeFind($mode, $query);
		$this->_Model =& $model;

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
		// verifica se há dados setados no modelo
		if(isset($this->_Model->data) && !empty($this->_Model->data))
		{
			return $this->__modelLocalize();
		}

		// caso tenha sido invocado em um Find (haja query de busca)
		if(!empty($query) && is_array($query))
		{
			return $this->__queryLocalize($query);
		}

		return true;
	}

		/**
	 * Localize data of Model->data array.
	 *
	 * @return bool $success
	 */
	private function __modelLocalize()
	{
		$status = true;

		foreach($this->_Model->data[$this->_Model->alias] as $field => $value)
		{
			if($this->__isUnLocalizableField($this->_Model, $field))
			{
				switch($this->_modelFields[$this->_Model->alias][$field])
				{
					case 'date':
					case 'datetime':
					case 'timestamp':
						$status = ($status && $this->__dateConvert($this->_Model->data[$this->_Model->alias][$field], $this->_modelFields[$this->_Model->alias][$field]));
						break;
					case 'number':
					case 'decimal':
					case 'float':
					case 'double':
						$status = ($status && $this->__decimal($this->_Model->data[$this->_Model->alias][$field]));
						break;
				}
			}
		}

		return $status;
	}

	/**
	 * Localize data of Model->find() 'conditions' array
	 *
	 * @param array $query Model->find conditions
	 * @return bool $success
	 */
	private function __queryLocalize(&$query)
	{
		$status = true;

		// don't support directly written SQL
		if(!is_array($query))
			return true;

		foreach($query as $field => &$value)
		{
			if(strtolower($field) === 'or' || strtolower($field) === 'and' || is_numeric($field))
			{
				$status = $status && $this->__queryLocalize($value);
				continue;
			}

			list($modelName, $field) = Utils::parseModelField($field);

			if($this->__isUnLocalizableField($this->_Model, $field, $value))
			{
				switch($this->_modelFields[$this->_Model->alias][$field])
				{
					case 'date':
					case 'datetime':
					case 'timestamp':
						if(is_array($value))
							foreach($value as &$v)
								$status = ($status && $this->__dateConvert($v, $this->_modelFields[$this->_Model->alias][$field]));
						else
							$status = ($status && $this->__dateConvert($value, $this->_modelFields[$this->_Model->alias][$field]));
						break;
					case 'decimal':
					case 'float':
					case 'double':
						if(is_array($value))
							foreach($value as &$v)
								$status = ($status && $this->__decimal($v));
						else
							$status = ($status && $this->__decimal($value));
						break;
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

			if(empty($d))
				return $value;

			$dt = new DateTime($d);
			$value = $dt->format($this->typesFormat[$this->_Model->useDbConfig][$type]);
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Convert a numeric value from a localized value to another
	 * locale.
	 *
	 * Ex.:
	 *  '1.000.000,22' -> '1000000.22'
	 *  '1.12' -> '1.12'
	 *  '1,12' -> '1.12'
	 *
	 * @param string $value
	 * @return bool success
	 */
	private function __decimal(&$value)
	{
		$value = Unlocalize::setLocale($this->systemLang)->decimal($value);

		return true;
	}

	/**
	 * Check if a field is 'un-localizable'
	 *
	 * @param Model $model
	 * @param string $field
	 * @return bool
	 */
	private function __isUnLocalizableField($model, $field, $value = null)
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
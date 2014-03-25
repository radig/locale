<?php
App::uses('Unlocalize', 'Locale.Lib');
App::uses('Utils', 'Locale.Lib');
App::uses('Formats', 'Locale.Lib');
/**
 * Behavior to automagic convert dates, numbers and currency from
 * any localized format to DB format for consistency.
 *
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
 * @copyright Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package radig.Locale
 * @subpackage Model.Behavior
 */
class LocaleBehavior extends ModelBehavior
{
	/**
	 * Modelo corrente
	 *
	 * @var Model
	 */
	private $_Model;

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
	 *
	 * @see ModelBehavior::setup()
	 */
	public function setup(Model $_Model, $config = array()) {
		$this->settings = array(
			'ignoreAutomagic' => true
		);

		$os = strtolower(php_uname('s'));

		if (strpos($os, 'windows') === false) {
			$this->systemLang = substr(setlocale(LC_ALL, "0"), 0, 5);
		} else {
			$winLocale = explode('.', setlocale(LC_CTYPE, "0"));
			$locale = array_search($winLocale[0], Formats::$windowsLocaleMap);

			$this->systemLang = null;

			if ($locale !== false) {
				$this->systemLang = $locale;
			}
		}

		$this->__checkConfig($_Model, $config);
	}

	/**
	 * Check and set missing configurations
	 *
	 * @param Model $model
	 * @param array $config
	 * @return void
	 */
	private function __checkConfig(Model $model, $config = null) {
		if ($config !== null || !isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = Set::merge($this->_defaultSettings, $config);
		}

		if (!isset($this->_modelFields[$model->alias])) {
			$this->_modelFields[$model->alias] = @$model->getColumnTypes();
		}

		if (isset($this->typesFormat[$model->useDbConfig])) {
			return;
		}

		$db = $model->getDataSource();
		$this->typesFormat[$model->useDbConfig] = array();

		foreach ($db->columns as $type => $info) {
			if (isset($info['format'])) {
				$this->typesFormat[$model->useDbConfig][$type] = $info['format'];
			}
		}
	}

	/**
	 *
	 * @see ModelBehavior::beforeValidate()
	 */
	public function beforeValidate(Model $model, $options = array()) {
		parent::beforeValidate($model, $options);
		$this->_Model = $model;

		$this->__checkConfig($model);
		$this->localizeData();

		// always allow model validation occours
		return true;
	}

	/**
	 *
	 * @see ModelBehavior::beforeSave()
	 */
	public function beforeSave(Model $model, $options = array()) {
		parent::beforeSave($model, $options);
		$this->_Model = $model;

		$this->__checkConfig($model);

		return $this->localizeData();
	}

	/**
	 *
	 * @see ModelBehavior::beforeFind()
	 */
	public function beforeFind(Model $model, $query) {
		parent::beforeFind($model, $query);
		$this->_Model = $model;

		$this->__checkConfig($model);

		$this->localizeData($query['conditions']);

		return $query;
	}

	/**
	 * Localize data, depending of your origin.
	 * If no data is provide, only return true
	 *
	 * @param array $query Conditions of a Model::find operation. Optional.
	 *
	 * @return bool $status True if sucess, false otherwise.
	 */
	public function localizeData(&$query = null) {
		if (isset($this->_Model->data) && !empty($this->_Model->data)) {
			return $this->__modelLocalize();
		}

		if (!empty($query) && is_array($query)) {
			return $this->__queryLocalize($query);
		}

		return true;
	}

	/**
	 * Localize data of Model->data array.
	 *
	 * @return bool $success
	 */
	private function __modelLocalize() {
		$status = true;

		foreach ($this->_Model->data[$this->_Model->alias] as $field => $value) {
			if ($this->__isUnLocalizableField($this->_Model, $field)) {
				switch ($this->_modelFields[$this->_Model->alias][$field]) {
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
	private function __queryLocalize(&$query) {
		$status = true;

		// don't support directly written SQL
		if (!is_array($query)) {
			return true;
		}

		foreach ($query as $field => &$value) {
			if (strtolower($field) === 'or' || strtolower($field) === 'and' || is_numeric($field)) {
				$status = $status && $this->__queryLocalize($value);
				continue;
			}

			list($modelName, $field) = Utils::parseModelField($field);

			if ($this->__isUnLocalizableField($this->_Model, $field, $value)) {
				switch ($this->_modelFields[$this->_Model->alias][$field]) {
					case 'date':
					case 'datetime':
					case 'timestamp':
						if (!is_array($value)) {
							$status = ($status && $this->__dateConvert($value, $this->_modelFields[$this->_Model->alias][$field]));
							continue;
						}

						foreach ($value as &$v) {
							$status = ($status && $this->__dateConvert($v, $this->_modelFields[$this->_Model->alias][$field]));
						}
						break;
					case 'decimal':
					case 'float':
					case 'double':
						if (!is_array($value)) {
							$status = ($status && $this->__decimal($value));
							continue;
						}

						foreach ($value as &$v) {
							$status = ($status && $this->__decimal($v));
						}

						break;
				}
			}
		}

		return $status;
	}

	/**
	 * Try to convert a Date/DateTime/Timestamp input
	 *
	 * @param string $value
	 * @param string $type A valid schema date type, like: 'date', 'datetime' or 'timestamp'
	 * @return bool $success
	 */
	private function __dateConvert(&$value, $type = 'date') {
		// both have same string format
		if ($type == 'datetime') {
			$type = 'timestamp';
		}

		try {
			$d = Unlocalize::setLocale($this->systemLang)->date($value, ($type != 'date'));

			if (empty($d)) {
				return true;
			}

			$dt = new DateTime($d);
			$value = $dt->format($this->typesFormat[$this->_Model->useDbConfig][$type]);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Wrapper to Unlocalize::decimal
	 *
	 * Convert a localized decimal/float to USA numeric
	 * format
	 *
	 * @param mixed $value A integer, float, double or numeric string input
	 *
	 * @return string $value
	 */
	private function __decimal(&$value) {
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
	private function __isUnLocalizableField($model, $field, $value = null) {
		if (!isset($this->settings[$model->alias])) {
			return false;
		}

		if ($value === null && isset($model->data[$model->alias][$field]) && empty($model->data[$model->alias][$field])) {
			return false;
		}

		if ($value !== null && empty($value)) {
			return false;
		}

		if (!isset($this->_modelFields[$model->alias][$field])) {
			return false;
		}

		if ($this->settings[$model->alias]['ignoreAutomagic'] && in_array($field, $this->cakeAutomagicFields)) {
			return false;
		}

		return true;
	}
}

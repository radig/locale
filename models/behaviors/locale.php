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

		$this->systemLang = substr(setlocale(LC_ALL, "0"), 0, 5);

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

				// caso o campo esteja vazio E não tenha um array como valor E o campo faz parte do schema
				if(!empty($value) && isset($this->model->_schema[$field]) && (!$this->settings['ignoreAutomagic'] || ($this->settings['ignoreAutomagic'] && !in_array($field, $this->cakeAutomagicFields))))
				{
					switch($this->model->_schema[$field]['type'])
					{
						case 'date':
						case 'datetime':
						case 'timestamp':
							if(is_array($value))
								foreach($value as &$v)
									$status = ($status && $this->__dateConvert($v, $this->model->_schema[$field]['type']));
							else
								$status = ($status && $this->__dateConvert($value, $this->model->_schema[$field]['type']));
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

            if(empty($d))
                return $value;

			$dt = new DateTime($d);
			$value = $dt->format($this->typesFormat[$type]);
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
	private function __stringToFloat(&$value)
	{
		$value = Unlocalize::setLocale($this->systemLang)->decimal($value);

		return true;
	}
}

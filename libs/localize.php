<?php

/**
 * Class to localize special data like dates, timestamps and numbers
 * in and out different formats.
 *
 */
class Localize
{
	/**
	 * Current locale for input data
	 *
	 * @var string
	 */
	static public $currentLocale = 'pt-br';

	/**
	 * Suported formats
	 * @var array
	 */
	static public $formats = array(
		'pt-br' => array(
			'date' => array(
				'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{2,4}/',
				'slices' => array('y' => 3, 'm' => 2, 'd' => 1)
			),
			'timestamp' => array(
				'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{2,4} \d{2}:\d{2}:\d{2}/',
				'slices' => array('y' => 3, 'm' => 2, 'd' => 1, 'h' => 4, 'i' => 5, 's' => 6)
			)
		)
	);

	/**
	 * Current instance
	 * @var Localize
	 */
	private static $_Instance = null;

	/**
	 * Singleton implementation
	 *
	 * @return Localize
	 */
    public static function getInstance()
    {
        if(self::$_Instance === null)
        {
            self::$_Instance = new self;
        }

        return self::$_Instance;
    }

    /**
     * Set locale of input data
     *
     * @param string $locale Name of locale, the same format of CakePHP Localization
     *
     * @return Localize Current instance of that class, for chaining methods
     */
	static public function setLocale($locale)
	{
		self::$currentLocale = $locale;

		return self::getInstance();
	}

	/**
	 * Include a new format to supported format list.
	 * You must provide an array like that:
	 *
	 * array(
	 * 		'date' => array(
	 * 			'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{2,4}/',
	 * 			'slices' => array('y' => 3, 'm' => 2, 'd' => 1)
	 * 		),
	 * 		'timestamp' => array(
	 *    		'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{2,4} \d{2}:\d{2}:\d{2}/',
	 *    		'slices' => array('y' => 3, 'm' => 2, 'd' => 1, 'h' => 4, 'i' => 5, 's' => 6)
	 *    	)
	 * )
	 *
	 * @param string $locale Name of locale, the same format of CakePHP Localization
	 * @param string $format An array like in the description
	 *
	 * @return Localize Current instance of that class, for chaining methods
	 */
	static public function addFormat($locale, $format)
	{
		self::$formats[$locale] = $format;

		return self::getInstance();
	}

	/**
	 * Converte uma data localizada para padrão de banco de dados (americano)
	 *
	 * @param string $value Your localized date
	 * @param string $format The output date format, the same syntaxe of date() function
	 * @param bool $includeTime If the input date include time info
	 *
	 * @return string Formatted date on Success, original Date on failure
	 */
	static public function date($value, $format, $includeTime = false)
	{
		if(!isset(self::$formats[self::$currentLocale]))
			throw new LocaleException('Localização não reconhecida pela Lib Localize. Tente adicionar o formato antes de usa-lo.');

		if(self::isNullDate($value))
			return $value;

		if(!$includeTime)
		{
			$currentFormat = self::$formats[self::$currentLocale]['date'];
			$slices = $currentFormat['slices'];
			$final = "\${$slices['y']}-\${$slices['m']}-\${$slices['d']}";
		}
		else
		{
			$currentFormat = self::$formats[self::$currentLocale]['timestamp'];
			$slices = $currentFormat['slices'];
			$final = "\${$slices['y']}-\${$slices['m']}-\${$slices['d']} \${$slices['h']}:\${$slices['i']}:\${$slices['s']}";
		}

		// transform localized date into iso formated date
		$iso = preg_replace($currentFormat['pattern'], $final, $value);

		try {
			$dt = new DateTime($iso);
			$value = $dt->format($format);
		}
		catch(Exception $e) {
			return $value;
		}

		return $valuess;
	}

	/**
	 * Util method to check if a date is the same of null
	 *
	 * @param string $value Date
	 * @return bool If is null or not
	 */
	static public function isNullDate($value)
	{
		return (empty($value) || strpos('0000-00-00', $value) !== false);
	}
}
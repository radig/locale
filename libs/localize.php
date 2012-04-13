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
				'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/',
				'slices' => array('y' => 3, 'm' => 2, 'd' => 1)
			),
			'timestamp' => array(
				'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{2,4}) (\d{2}):(\d{2}):(\d{2})/',
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
	 * Convert a localized date/timestamp to USA format date/timestamp
	 *
	 * @param string $value Your localized date
	 * @param string $format The output date format, the same syntaxe of date() function
	 * @param bool $includeTime If the input date include time info
	 *
	 * @return mixed a string formatted date on Success, original Date on failure or null case
	 * date is null equivalent
	 */
	static public function date($value, $format, $includeTime = false)
	{
		if(!isset(self::$formats[self::$currentLocale]))
			throw new LocaleException('Localização não reconhecida pela Lib Localize. Tente adicionar o formato antes de usa-lo.');

		if(self::isNullDate($value))
			return null;

		$iso = $value;
		if(!self::isISODate($value))
		{
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
		}

		try {
			$dt = new DateTime($iso);
			$value = $dt->format($format);
		}
		catch(Exception $e) {
			return $value;
		}

		return $value;
	}

	/**
	 * Convert a localized decimal/float to USA numeric
	 * format
	 *
	 * @param mixed $value A integer, float, double or numeric string input
	 *
	 * @return string $value
	 */
	static public function decimal($value)
	{
		if(empty($value))
			return $value;

		$v = (string)$value;

		$currentFormat = localeconv();

		$integer = $v;
		$decimal = 0;

		$decimalPoint = strrpos($v, $currentFormat['decimal_point']);
		if($decimalPoint !== false)
		{
			$decimal = substr($v, $decimalPoint + 1);

			$integer = substr($v, 0, $decimalPoint);
			$integer = preg_replace('/[\.|,]/', '', $integer);
		}

		$value = $integer;
		if($decimal > 0)
			$value .= '.' . $decimal;

		return $value;
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

	/**
	 * Check if a date is valid iso format date
	 *
	 * @param string $value Date
	 * @return bool If is or not a ISO formated date
	 */
	static public function isISODate($value)
	{
		$isoPattern = '/^\d{4}-\d{2}-\d{2}/';
		if(preg_match($isoPattern,$value) === 0)
			return false;

		$month = substr($value, 4, 2);
		if($month < 1 || $month > 12)
			return false;

		return true;
	}
}
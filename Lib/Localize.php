<?php
App::uses('LocaleException', 'Locale.Lib');
App::uses('Formats', 'Locale.Lib');
App::uses('Utils', 'Locale.Lib');
/**
 * Class to "localize" special data like dates, timestamps and numbers
 * from US/ISO format.
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
 * @subpackage Lib
 */
class Localize {
	/**
	 * Current locale for output data
	 *
	 * @var string
	 */
	static public $currentLocale = 'pt_BR';

	/**
	 * Set locale of output data
	 *
	 * @param string $locale Name of locale, the same format of setlocale php function
	 *
	 * @return Localize Current instance of that class, for chaining methods
	 */
	static public function setLocale($locale) {
		$locales = array($locale . '.utf-8', $locale . '.UTF-8', $locale);

		$os = strtolower(php_uname('s'));
		if (strpos($os, 'windows') !== false) {
			$locales = array(Formats::$windowsLocaleMap[$locale]);
		}

		if (!setlocale(LC_ALL, $locales)) {
			throw new LocaleException("Locale {$locale} não disponível no seu sistema.");
		}

		self::$currentLocale = $locale;

		return new static;
	}

	/**
	 * Wrapper to Formats::addOutput.
	 *
	 * @param string $locale Name of locale, the same format of setlocale php function
	 * @param string $format An array like in the description
	 *
	 * @return Localize Current instance of that class, for chaining methods
	 */
	static public function addFormat($locale, $format) {
		Formats::addOutput($locale, $format);
		return new static;
	}

	/**
	 * Try read $locale Format. If not exist, return NULL
	 *
	 * @param  array $locale Name of locale, the same format of setlocale php function
	 * @return mixed array if exist, null otherwise
	 */
	static public function getFormat($locale) {
		if (isset(Formats::$output[$locale])) {
			return Formats::$output[$locale];
		}

		return null;
	}

	/**
	 *
	 * @return string
	 */
	static public function date($value) {
		if (Utils::isNullDate($value)) {
			return '';
		}

		$dateTime = Utils::initDateTime($value);

		return $dateTime->format(Formats::$output[self::$currentLocale]['small']);
	}

	/**
	 *
	 * @param string $value Date string value
	 * @param bool $seconds Include seconds?
	 *
	 * @return string Localized date time
	 */
	static public function dateTime($value, $seconds = true) {
		if (Utils::isNullDate($value)) {
			return '';
		}

		$dateTime = Utils::initDateTime($value);
		$format = Formats::$output[self::$currentLocale]['full'];

		if ($seconds !== true) {
			$format = substr($format, 0, -2);
		}

		return $dateTime->format($format);
	}

	/**
	 *
	 * @param string $dateTime
	 * @param string $displayTime
	 * @param string $format
	 */
	static public function dateLiteral($value, $displayTime = false, $format = null)
	{
		if (Utils::isNullDate($value)) {
			return '';
		}

		$dateTime = Utils::initDateTime($value);

		if ($format === null) {
			$format = Formats::$output[self::$currentLocale]['literal'];

			if ($displayTime) {
				$format = Formats::$output[self::$currentLocale]['literalWithTime'];
			}
		}

		return strftime($format, $dateTime->format('U'));
	}

	/**
	 *
	 * @param number $value
	 * @return string
	 */
	static public function currency($value) {
		$currentFormat = localeconv();

		$number = Utils::numberFormat($value, 2, true, $currentFormat['mon_decimal_point'], $currentFormat['mon_thousands_sep']);

		if ($number === false) {
			return $value;
		}

		return $currentFormat['currency_symbol'] . ' ' . $number;
	}

	/**
	 *
	 *
	 * @param numeric $value
	 * @param int $precision If null, precision is set with 2 decimals
	 * @param bool $thousands
	 *
	 * @return numeric
	 */
	static function number($value, $precision = null, $thousands = false) {
		$currentFormat = localeconv();

		$number = Utils::numberFormat($value, $precision, $thousands, $currentFormat['decimal_point'], $currentFormat['thousands_sep']);

		if ($number === false) {
			return $value;
		}

		return $number;
	}
}

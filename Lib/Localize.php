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
 * @copyright 2009-2013, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Radig
 * @subpackage Radig.Locale.Lib
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
	 * Format a ISO date as localized date string
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
	 * Format a ISO date and time as localized date and time string
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
	 * Format a ISO date as localized human-friendly date string
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
	 * Format float/integer as currency
	 *
	 * @param number $value
	 * @return string
	 */
	static public function currency($value) {
		if (!is_numeric($value)) {
			return $value;
		}

		$formatter = new NumberFormatter(self::$currentLocale, NumberFormatter::CURRENCY);
		$symbolCode = $formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE);

		return $formatter->formatCurrency($value, $symbolCode);
	}

	/**
	 * Format float/integer as a localized decimal/integer
	 *
	 * Just for BC, $precision default is 2. Will be removed in future version.
	 *
	 * @param numeric $value
	 * @param int $precision DEPRECATED
	 * @param bool $thousands DEPRECATED
	 *
	 * @return numeric
	 */
	static function number($value, $precision = null, $thousands = false) {
		if (!is_numeric($value)) {
			return $value;
		}

		$formatter = new NumberFormatter(self::$currentLocale, NumberFormatter::DECIMAL);

		if ($precision === null) {
			$precision = 2;
		}
		$formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);

		if (!$thousands) {
			$formatter->setAttribute(NumberFormatter::GROUPING_SIZE, 0);
		}

		return $formatter->format($value);
	}
}

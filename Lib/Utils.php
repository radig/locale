<?php
/**
 * Generic methods for decimals/dates format
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
class Utils {
	/**
	 * Check if a date is the same of null
	 *
	 * @param string $value Date
	 *
	 * @return bool If is null or not
	 */
	static public function isNullDate($value) {
		return (empty($value) || strpos($value, '0000-00-00') !== false);
	}

	/**
	 * Check if a date is valid iso format date
	 *
	 * @param string $value Date
	 *
	 * @return bool If is or not a ISO formated date
	 */
	static public function isISODate($value) {
		$isoPattern = '/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/';

		if (preg_match($isoPattern, $value) === 0) {
			return false;
		}

		$month = substr($value, 5, 2);
		if ($month < 1 || $month > 12) {
			return false;
		}

		return true;
	}

	/**
	 * Return either DateTime with '$value' date if that is valid,
	 * current date if isn't.
	 *
	 * @param string $value User required date
	 *
	 * @return DateTime with a valid date
	 */
	static public function initDateTime($value) {
		if (self::isNullDate($value)) {
			return new DateTime();
		}

		try {
			return new DateTime($value);
		}
		catch (Exception $e) {
			return new DateTime();
		}
	}

	/**
	 * Replacement function for number_format, with extras:
	 *  - Use truncate over round
	 *  - Optional use of thousands
	 *
	 * @param mixed $value
	 * @param int $precision
	 * @param bool $thousands
	 * @param string $decimalSep
	 * @param string $thousandSep
	 * @param bool $round
	 *
	 * @return mixed String numeric representation in success and False boolean on
	 * invalid numeric values.
	 */
	static public function numberFormat($value, $precision = null, $thousands = false, $decimalSep = '.', $thousandSep = ',', $round = false) {
		if ($precision === null) {
			$precision = 2;
		}

		if (!is_string($value)) {
			$oldLocale = setlocale(LC_NUMERIC, "0");
			setlocale(LC_NUMERIC, 'en_US');
			$value = number_format($value, $precision);
			setlocale(LC_NUMERIC, $oldLocale);
		}

		$value = str_replace(',', '', $value);

		if (empty($value) || !is_numeric($value)) {
			return false;
		}

		$parts = explode('.', $value);

		if (count($parts) == 2) {
			$int = (string)$parts[0];
			$dec = str_pad((string)$parts[1], $precision, '0', STR_PAD_RIGHT);
		} else {
			$int = (string)$parts[0];
			$dec = str_repeat('0', $precision);
		}

		if ($round) {
			$dec = round('0.' . $dec, $precision);
			$dec = str_pad(substr($dec, 2, $precision), $precision, '0', STR_PAD_RIGHT);;
		} else {
			$dec = substr($dec, 0, $precision);
		}

		if ($thousands) {
			$copy = '';
			for ($l = strlen($int) - 1, $c = 0; $l >= 0; $l--, $c++) {
				$copy = $int[$l] . $copy;

				if ($c === 2 && $l > 0) {
					$c = -1;
					$copy = $thousandSep . $copy;
				}
			}
			$int = $copy;
		}

		$number = $int;

		if (!empty($dec)) {
			$number .= $decimalSep . $dec;
		}

		return $number;
	}

	/**
	 * Extract Model and field name from a conditions
	 * consult string.
	 *
	 * @param string $raw Query correspondent field
	 * @return array Two position array with Model and field name
	 * respectively
	 */
	static public function parseModelField($raw) {
		$validField = '/^([a-zA-Z][a-zA-Z0-9]*)(\\.[a-zA-Z][a-zA-Z0-9]*)?/';
		$matched = array();

		$field = $raw;
		$modelName = '';

		if (preg_match($validField, $raw, $matched) === 1) {
			$field = $matched[0];

			// If condition have Model.field sintax
			if (strpos($field, '.') !== false) {
				$ini = strpos($field, '.');

				$modelName = substr($field, 0, $ini);
				$field = substr($field, $ini + 1);
			}

		}

		return array($modelName, $field);
	}
}

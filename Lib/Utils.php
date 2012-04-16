<?php
/**
 * Generic methods for decimals/dates format
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
 * @package Radig
 * @subpackage Radig.Locale.Lib
 */
class Utils
{
	/**
	 * Check if a date is the same of null
	 *
	 * @param string $value Date
	 *
	 * @return bool If is null or not
	 */
	static public function isNullDate($value)
	{
		return (empty($value) || strpos($value, '0000-00-00') !== false);
	}

	/**
	 * Check if a date is valid iso format date
	 *
	 * @param string $value Date
	 *
	 * @return bool If is or not a ISO formated date
	 */
	static public function isISODate($value)
	{
		$isoPattern = '/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/';

		if(preg_match($isoPattern, $value) === 0)
			return false;

		$month = substr($value, 5, 2);
		if($month < 1 || $month > 12)
			return false;

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
	static public function initDateTime($value)
	{
		if(self::isNullDate($value))
			return new DateTime();

		try
		{
			return new DateTime($value);
		}
		catch(Exception $e)
		{
			return new DateTime();
		}
	}
}
<?php
App::uses('AppHelper', 'View/Helper');
App::uses('Localize', 'Locale.Lib');
App::uses('Formats', 'Locale.Lib');
/**
 * Helper to localized formatting dates, numbers and currency from databases format.
 *
 * Based on Juan Basso cake_ptbr plugin: http://github.com/jrbasso/cake_ptbr
 *
 * -----
 * Helper para formatação localizada de datas, números decimais e valores monetários.
 *
 * Baseado no plugin cake_ptbr do Juan Basso: http://github.com/jrbasso/cake_ptbr
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
 * @subpackage View.Helper
 */
class LocaleHelper extends AppHelper
{
	/**
	 * All helper default options
	 *
	 * @var array
	 */
	protected $_settings = array(
		'locale' => null,
	);

	/**
	 * Current options
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 *
	 * @param View $View
	 * @param array $settings
	 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		if (isset($settings) && is_array($settings)) {
			$this->settings = array_merge($this->_settings, $settings);
		}

		if (!empty($this->settings['locale'])) {
			return;
		}

		$os = strtolower(php_uname('s'));
		if (strpos($os, 'windows') === false) {
			$this->settings['locale'] = substr(setlocale(LC_CTYPE, "0"), 0, 5);
			return;
		}

		$winLocale = explode('.', setlocale(LC_CTYPE, "0"));
		$locale = array_search($winLocale[0], Formats::$windowsLocaleMap);

		if ($locale !== false) {
			$this->settings['locale'] = $locale;
		}
	}

	/**
	 * Wrapper to Localize::date
	 *
	 * @param string $date
	 *
	 * @return string Localized date
	 */
	public function date($date = null) {
		return Localize::setLocale($this->settings['locale'])
				->date($date);
	}

	/**
	 * Wrapper to Localize::dateTime
	 *
	 * @param string $dateTime
	 * @param bool $seconds
	 *
	 * @return string Localized date with time
	 */
	public function dateTime($dateTime, $seconds = true) {
		return Localize::setLocale($this->settings['locale'])
				->dateTime($dateTime, $seconds);
	}

	/**
	 * Wrapper to Localize::dateLiteral
	 *
	 * @param string $dateTime
	 * @param string $displayTime
	 * @param string $format
	 *
	 * @return string Localized date literal
	 */
	public function dateLiteral($dateTime, $displayTime = false, $format = null) {
		return Localize::setLocale($this->settings['locale'])
				->dateLiteral($dateTime, $displayTime, $format);
	}

	/**
	 * Wrapper to Localize::currency
	 *
	 * @param number $value
	 *
	 * @return string Localized currency
	 */
	public function currency($value) {
		return Localize::setLocale($this->settings['locale'])
				->currency($value);
	}

	/**
	 * Wrapper to Localize::number
	 *
	 * @param number $value
	 * @param int $precision
	 * @param boolean $thousands
	 *
	 * @return number Localized numeber
	 */
	public function number($value, $precision = 2, $thousands = false) {
		return Localize::setLocale($this->settings['locale'])
				->number($value, $precision, $thousands);
	}
}

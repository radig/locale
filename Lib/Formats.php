<?php
App::uses('LocaleException', 'Locale.Lib');
/**
 * Manager of supported localize and unlocalize formats
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
class Formats {
	static public $windowsLocaleMap = array(
		'pt_BR' => 'Portuguese_Brazil',
		'en_US' => 'English_United States'
	);


	/**
	 * Suported input formats
	 *
	 * @var array
	 */
	static public $input = array(
		'en_US' => array(
			'date' => array(
				'pattern' => '/^(\d{2,4})\/(\d{1,2})\/(\d{1,2})$/',
				'slices' => array('y' => 1, 'm' => 2, 'd' => 3)
			),
			'timestamp' => array(
				'pattern' => '/^(\d{2,4})\/(\d{1,2})\/(\d{1,2}) (\d{2}):(\d{2})(:(\d{2}))?$/',
				'slices' => array('y' => 1, 'm' => 2, 'd' => 3, 'h' => 4, 'i' => 5, 's' => 7)
			)
		),
		'pt_BR' => array(
			'date' => array(
				'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/',
				'slices' => array('y' => 3, 'm' => 2, 'd' => 1)
			),
			'timestamp' => array(
				'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{2,4}) (\d{2}):(\d{2})(:(\d{2}))?$/',
				'slices' => array('y' => 3, 'm' => 2, 'd' => 1, 'h' => 4, 'i' => 5, 's' => 7)
			)
		)
	);

	/**
	 * Suported output formats
	 *
	 * @var array
	 */
	static public $output = array(
		'en_US' => array(
			'small' => 'Y-m-d',
			'literal' => '%a %d %b %Y',
			'literalWithTime' => '%a %d %b %Y %T',
			'full' => 'Y-m-d H:i:s'
		),
		'pt_BR' => array(
			'small' => 'd/m/Y',
			'literal' => '%A, %e de %B de %Y',
			'literalWithTime' => '%A, %e de %B de %Y, %T',
			'full' => 'd/m/Y H:i:s'
		)
	);

	/**
	 * Include a new format to supported input format list.
	 * You must provide an array like that:
	 *
	 * array(
	 * 		'date' => array(
	 * 			'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{2,4}$/',
	 * 			'slices' => array('y' => 3, 'm' => 2, 'd' => 1)
	 * 		),
	 * 		'timestamp' => array(
	 *    		'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{2,4} \d{2}:\d{2}:\d{2}$/',
	 *    		'slices' => array('y' => 3, 'm' => 2, 'd' => 1, 'h' => 4, 'i' => 5, 's' => 6)
	 *    	)
	 * )
	 *
	 * @param string $locale A locale string, like used in setlocale function
	 * @param array $formats array
	 */
	static public function addInput($locale, $formats) {
		$requireds = array('date', 'timestamp');

		foreach ($requireds as $required) {
			if (array_key_exists($required, $formats) === true &&
				isset($formats[$required]['pattern']) &&
				isset($formats[$required]['slices'])) {
					continue;
			}

			throw new LocaleException('Você deve fornecer todas as chaves do formato para usa-lo.');
		}

		self::$input[$locale] = $formats;
	}

	/**
	 * Include a new format to supported output format list.
	 * You must provide an array like that:
	 *
	 * array(
	 * 		'small' => 'Y-m-d',
	 *		'literal' => '%a %d %b %Y',
	 *		'literalWithTime' => '%a %d %b %Y %T',
	 *		'full' => 'Y-m-d H:i:s'
	 * )
	 *
	 * @param string $locale A locale string, like used in setlocale function
	 * @param array $formats array
	 */
	static public function addOutput($locale, $formats) {
		$requireds = array('small', 'literal', 'literalWithTime', 'full');

		foreach ($requireds as $required) {
			if (array_key_exists($required, $formats) === false) {
				throw new LocaleException('Você deve fornecer todas as chaves do formato para usa-lo.');
			}
		}

		self::$output[$locale] = $formats;
	}
}

<?php
/**
 * Plugin specific exceptions
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
class LocaleException extends Exception {
	public function __construct($message, $code = 1) {
		parent::__construct(__($message, true), $code);
	}
}

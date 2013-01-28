<?php
class LocaleException extends Exception {
	public function __construct($message, $code = 1) {
		parent::__construct(__($message, true), $code);
	}
}

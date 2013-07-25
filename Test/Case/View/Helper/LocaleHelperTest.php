<?php
App::uses('LocaleHelper', 'Locale.View/Helper');
App::uses('Controller', 'Controller');
App::uses('View', 'View');
/**
 * Testes do Helper Locale
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2013, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Radig.Locale
 * @subpackage Radig.Locale.Test.Case.View.Helper
 */
class LocaleHelperCase extends CakeTestCase {
	public $Locale = null;

	/**
	 * setUp
	 *
	 * @retun void
	 * @access public
	 */
	public function setUp() {
		parent::setUp();

		$this->Controller = new Controller(null);
		$this->View = new View($this->Controller);
		$this->Locale = new LocaleHelper($this->View, array('locale' => 'pt_BR'));
	}

	public function testConstruct() {
		$this->Locale = new LocaleHelper($this->View, array());
		$this->assertNotEquals($this->Locale->settings, array('locale' => ''));

		$this->Locale = new LocaleHelper($this->View, array('locale' => 'pt_BR'));
		$this->assertEquals($this->Locale->settings, array('locale' => 'pt_BR'));
	}

	/**
	 * testDate
	 *
	 * @retun void
	 * @access public
	 */
	public function testDate() {
		$this->assertEquals($this->Locale->date(), '');
		$this->assertEquals($this->Locale->date('2009-04-21'), '21/04/2009');
		$this->assertEquals($this->Locale->date('invalido'), date('d/m/Y'));
	}

	/**
	 * testNullDate
	 *
	 * @return void
	 */
	public function testNullDate() {
		$this->assertEquals($this->Locale->date('0000-00-00'), '');
	}

	/**
	 * testDateTime
	 *
	 * @retun void
	 * @access public
	 */
	public function testDateTime() {
		$this->assertEquals($this->Locale->dateTime('2010-08-26 16:12:40'), '26/08/2010 16:12:40');
		$this->assertEquals($this->Locale->dateTime('2010-08-26 16:12:40', false), '26/08/2010 16:12');
		$this->assertEquals($this->Locale->dateTime('0000-00-00 00:00:00', false), '');
	}

	/**
	 * testDateLiteral
	 *
	 * @retun void
	 * @access public
	 */
	public function testDateLiteral() {
		$this->assertEquals($this->Locale->dateLiteral('2010-08-26 16:12:40'), 'quinta, 26 de agosto de 2010');
		$this->assertEquals($this->Locale->dateLiteral('2010-08-26 16:12:40', true), 'quinta, 26 de agosto de 2010, 16:12:40');

		$this->assertEquals($this->Locale->dateLiteral('0000-00-00 00:00:00', false), '');
	}

	public function testCurrency() {
		$this->assertEquals($this->Locale->currency('12.45'), 'R$ 12,45');
		$this->assertEquals($this->Locale->currency('1,234.45'), 'R$ 1.234,45');
		$this->assertEquals($this->Locale->currency('1,234,567.45'), 'R$ 1.234.567,45');
		$this->assertEquals($this->Locale->currency('-'), '-');
	}

	public function testNumber() {
		$this->assertEquals($this->Locale->number('12'), '12,00');
		$this->assertEquals($this->Locale->number('12', 0), '12');
		$this->assertEquals($this->Locale->number('12.45'), '12,45');
		$this->assertEquals($this->Locale->number('12.82319', 4), '12,8231');
		$currentLocale = localeconv();
		$this->assertEquals($this->Locale->number('350020.123', 4, true), '350'. $currentLocale['thousands_sep'] .'020,1230');
		$this->assertEquals($this->Locale->number('-'), '-');
	}

	public function testUSACurrency() {
		$this->Locale = new LocaleHelper($this->View, array('locale' => 'en_US'));

		$this->assertEquals($this->Locale->currency('12.45'), '$ 12.45');
		$this->assertEquals($this->Locale->currency('1,234.45'), '$ 1,234.45');
	}

	public function testUSANumber() {
		$this->Locale = new LocaleHelper($this->View, array('locale' => 'en_US'));

		$this->assertEquals($this->Locale->number('12'), '12.00');
		$this->assertEquals($this->Locale->number('12', 0), '12');
		$this->assertEquals($this->Locale->number('12.45'), '12.45');
		$this->assertEquals($this->Locale->number('12.82319', 4), '12.8231');
		$this->assertEquals($this->Locale->number('350020.123', 4, true), '350,020.1230');
		$this->assertEquals($this->Locale->number('-'), '-');
	}

	/**
	 * testLocaleWithParameter
	 *
	 * @retun void
	 * @access public
	 */
	public function testLocaleWithParameter() {
		$this->assertEquals($this->Locale->date('2009-04-21'), '21/04/2009');
		$this->assertEquals($this->Locale->dateTime('2010-08-26 16:12:40'), '26/08/2010 16:12:40');
		$this->assertEquals($this->Locale->dateTime('2010-08-26 16:12:40', false), '26/08/2010 16:12');
		$this->assertEquals($this->Locale->dateLiteral('2010-08-26 16:12:40'), 'quinta, 26 de agosto de 2010');
		$this->assertEquals($this->Locale->dateLiteral('2010-08-26 16:12:40', true), 'quinta, 26 de agosto de 2010, 16:12:40');
		$this->assertEquals($this->Locale->number('12.53'), '12,53');
	}
}

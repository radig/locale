<?php
/**
 * Tests of Locale Helper
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2011, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Helper', 'Locale.Locale');
class LocaleCase extends CakeTestCase
{
	public $Locale = null;

	/**
	 * setUp
	 *
	 * @retun void
	 * @access public
	 */
	public function startCase()
	{
		parent::startCase();
		$this->Locale = new LocaleHelper();
	}

	/**
	 * testDate
	 *
	 * @retun void
	 * @access public
	 */
	public function testDate()
	{
		$this->assertEqual($this->Locale->date(), '');
		$this->assertEqual($this->Locale->date('2009-04-21'), '21/04/2009');
		$this->assertEqual($this->Locale->date('invalido'), date('d/m/Y'));
	}

	/**
	 * testNullDate
	 *
	 * @return void
	 */
	public function testNullDate()
	{
		$this->assertEqual($this->Locale->date('0000-00-00'), '');
	}

	/**
	 * testDateTime
	 *
	 * @retun void
	 * @access public
	 */
	public function testDateTime()
	{
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40'), '26/08/2010 16:12:40');
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40', false), '26/08/2010 16:12');
		$this->assertEqual($this->Locale->dateTime('0000-00-00 00:00:00', false), '');
	}

	/**
	 * testDateLiteral
	 *
	 * @retun void
	 * @access public
	 */
	public function testDateLiteral()
	{
		$this->assertEqual($this->Locale->dateLiteral('2010-08-26 16:12:40'), 'quinta, 26 de agosto de 2010');
		$this->assertEqual($this->Locale->dateLiteral('2010-08-26 16:12:40', true), 'quinta, 26 de agosto de 2010, 16:12:40');
		$this->assertEqual($this->Locale->dateLiteral('0000-00-00 00:00:00', false), '');
	}

	public function testCurrency()
	{
		$this->assertEqual($this->Locale->currency('12.45'), 'R$ 12,45');
		$this->assertEqual($this->Locale->currency('1,234.45'), 'R$ 1.234,45');
		$this->assertEqual($this->Locale->currency('1,234,567.45'), 'R$ 1.234.567,45');
		$this->assertEqual($this->Locale->currency('-'), '-');
	}


	public function testUSACurrency()
	{
		$this->Locale = new LocaleHelper(array('locale' => 'en_US'));
		$this->assertEqual($this->Locale->currency('12.45'), '$ 12.45');
		$this->assertEqual($this->Locale->currency('1,234.45'), '$ 1,234.45');
	}

	public function testNumber()
	{
		$this->Locale = new LocaleHelper(array('locale' => 'pt_BR'));
		$this->assertEqual($this->Locale->number('12'), '12,00');
		$this->assertEqual($this->Locale->number('12', 0), '12');
		$this->assertEqual($this->Locale->number('12.45'), '12,45');
		$this->assertEqual($this->Locale->number('12.82319', 4), '12,8231');
		$this->assertEqual($this->Locale->number('350020.123', 4, true), '350.020,1230');
		$this->assertEqual($this->Locale->number('-'), 0);
	}

	/**
	 * testLocaleWithParameter
	 *
	 * @retun void
	 * @access public
	 */
	public function testLocaleWithParameter()
	{
		$this->Locale = new LocaleHelper(array('locale' => 'pt_BR'));
		$this->assertEqual($this->Locale->date(), '');
		$this->assertEqual($this->Locale->date('2009-04-21'), '21/04/2009');
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40'), '26/08/2010 16:12:40');
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40', false), '26/08/2010 16:12');
		$this->assertEqual($this->Locale->dateLiteral('2010-08-26 16:12:40'), 'quinta, 26 de agosto de 2010');
		$this->assertEqual($this->Locale->dateLiteral('2010-08-26 16:12:40', true), 'quinta, 26 de agosto de 2010, 16:12:40');
		$this->assertEqual($this->Locale->number('12.53'), '12,53');
	}
}
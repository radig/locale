<?php
App::uses('Localize', 'Locale.Lib');
/**
 * Test for Localize Lib
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2013, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class LocalizeCase extends CakeTestCase
{
	/**
	 * setUp
	 *
	 * @retun void
	 * @access public
	 */
	public function setUp()
	{
		parent::setUp();
	}

	/**
	 * testAddFormat
	 *
	 * @return void
	 */
	public function testAddFormat()
	{
		$format = array(
			'small' => '',
			'literal' => '',
			'literalWithTime' => '',
			'full' => ''
		);

		Localize::addFormat('es_ES', $format);
		$this->assertEqual($format, Localize::getFormat('es_ES'));

		$this->assertNull(Localize::getFormat('en_ES'));
	}

	/**
	 * testNullDate
	 *
	 * @return void
	 */
	public function testNullDate()
	{
		Localize::setLocale('pt_BR');

		$this->assertEqual(Localize::date(null), '');
		$this->assertEqual(Localize::date(''), '');
		$this->assertEqual(Localize::date('0000-00-00'), '');
		$this->assertEqual(Localize::date('0000-00-00 00:00:00'), '');
	}

	/**
	 * testLocalizedDate
	 *
	 * @retun void
	 * @access public
	 */
	public function testLocalizedDate()
	{
		Localize::setLocale('pt_BR');

		$this->assertEqual(Localize::date('21/04/2009'), date('d/m/Y'));
		$this->assertEqual(Localize::date('01/03/1987'), '03/01/1987');
	}

	/**
	 * testBrDate
	 *
	 * @retun void
	 * @access public
	 */
	public function testBrDate()
	{
		Localize::setLocale('pt_BR');

		$this->assertEqual(Localize::date('2009-04-21'), '21/04/2009');

		$this->assertEqual(Localize::date('1987-03-01'), '01/03/1987');
		$this->assertEqual(Localize::date('1987-3-1'), '01/03/1987');
	}

	/**
	 * testBrDateTime
	 *
	 * @retun void
	 * @access public
	 */
	public function testBrDateTime()
	{
		Localize::setLocale('pt_BR');

		$this->assertEqual(Localize::dateTime(null), '');

		$this->assertEqual(Localize::dateTime('2009-04-21 12:03:01'), '21/04/2009 12:03:01');
		$this->assertEqual(Localize::dateTime('2009-4-21 23:59:59'), '21/04/2009 23:59:59');

		$this->assertEqual(Localize::dateTime('1987-03-01 12:03:01'), '01/03/1987 12:03:01');
		$this->assertEqual(Localize::dateTime('1987-3-1 23:59:59', true), '01/03/1987 23:59:59');
		$this->assertEqual(Localize::dateTime('1987-3-1 23:59:59', false), '01/03/1987 23:59');
	}

	public function testDateLiteral()
	{
		Localize::setLocale('pt_BR');

		$this->assertEqual(Localize::dateLiteral('2010-08-26 16:12:40'), 'quinta, 26 de agosto de 2010');
		$this->assertEqual(Localize::dateLiteral('2010-08-26 16:12:40', true), 'quinta, 26 de agosto de 2010, 16:12:40');
		$this->assertEqual(Localize::dateLiteral('0000-00-00 00:00:00', false), '');
	}

	public function testCurrency()
	{
		Localize::setLocale('pt_BR');

		$this->assertEqual(Localize::currency('12.45'), 'R$ 12,45');
		$this->assertEqual(Localize::currency('0.50'), 'R$ 0,50');
		$this->assertEqual(Localize::currency('1,234.45'), 'R$ 1.234,45');
		$this->assertEqual(Localize::currency('1,234,567.45'), 'R$ 1.234.567,45');
		$this->assertEqual(Localize::currency('-'), '-');
	}

	public function testUSACurrency()
	{
		Localize::setLocale('en_US');

		$this->assertEqual(Localize::currency('12.45'), '$ 12.45');
		$this->assertEqual(Localize::currency('0.50'), '$ 0.50');
		$this->assertEqual(Localize::currency('1,234.45'), '$ 1,234.45');
	}

	public function testUsDate()
	{
		Localize::setLocale('en_US');

		$this->assertEqual(Localize::date('2009-04-21'), '2009-04-21');
		$this->assertEqual(Localize::date('1987-03-01'), '1987-03-01');
	}

	public function testBrDecimals()
	{
		Localize::setLocale('pt_BR');
		$currentLocale = localeconv();

		$this->assertEqual(Localize::number('1'), '1,00');
		$this->assertEqual(Localize::number('23'), '23,00');
		$this->assertEqual(Localize::number('0.5'), '0,50');

		$this->assertEqual(Localize::number('25.32'), '25,32');
		$this->assertEqual(Localize::number('25.32', 1), '25,3');
		$this->assertEqual(Localize::number('25.32', 0), '25');

		$this->assertEqual(Localize::number('1,300.52'), '1300,52');
		$this->assertEqual(Localize::number('1,300.52', null, true), '1' . $currentLocale['thousands_sep'] . '300,52');

		$this->assertEqual(Localize::number('3,965,300.52'), '3965300,52');
		$this->assertEqual(Localize::number('3,965,300.52', 1, true), '3' . $currentLocale['thousands_sep'] . '965' . $currentLocale['thousands_sep'] .'300,5');
	}

	public function testUsDecimals()
	{
		Localize::setLocale('en_US');

		$this->assertIdentical(Localize::number('1'), '1.00');
		$this->assertIdentical(Localize::number('23'), '23.00');
		$this->assertIdentical(Localize::number('0.5'), '0.50');

		$this->assertIdentical(Localize::number('25.32'), '25.32');
		$this->assertIdentical(Localize::number('25.32', 1), '25.3');
		$this->assertIdentical(Localize::number('25.32', 0), '25');

		$this->assertIdentical(Localize::number('1,300.52'), '1300.52');
		$this->assertIdentical(Localize::number('1,300.52', null, true), '1,300.52');

		$this->assertIdentical(Localize::number('3,965,300.52'), '3965300.52');
		$this->assertIdentical(Localize::number('3,965,300.52', 1, true), '3,965,300.5');
	}
}

<?php
App::uses('Unlocalize', 'Locale.Lib');
/**
 * Test for Unlocalized Lib
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2013, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class UnlocalizeCase extends CakeTestCase
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
			'date' => array('pattern' => '', 'slices' => ''),
			'timestamp' => array('pattern' => '', 'slices' => '')
		);

		Unlocalize::addFormat('es_ES', $format);
		$this->assertEqual($format, Unlocalize::getFormat('es_ES'));
		$this->assertNull(Unlocalize::getFormat('en_ES'));
	}

	/**
	 * testNormalizeDate
	 *
	 * @return void
	 */
	public function testNormalizeDate()
	{
		$this->assertEqual(Unlocalize::normalizeDate('1987-3-1'), '1987-03-01');
		$this->assertEqual(Unlocalize::normalizeDate('87-3-1'), '1987-03-01');
		$this->assertEqual(Unlocalize::normalizeDate('09-12-1'), '2009-12-01');
		$this->assertEqual(Unlocalize::normalizeDate('29-02-1'), '2029-02-01');
		$this->assertEqual(Unlocalize::normalizeDate('31-02-1'), '1931-02-01');

		$this->assertEqual(Unlocalize::normalizeDate('31-02-1 12:30:20'), '1931-02-01 12:30:20');
	}

	/**
	 * testNullDate
	 *
	 * @return void
	 */
	public function testNullDate()
	{
		Unlocalize::setLocale('pt_BR');

		$this->assertEqual(Unlocalize::date(null), null);
		$this->assertEqual(Unlocalize::date(''), null);
		$this->assertEqual(Unlocalize::date('0000-00-00'), null);
		$this->assertEqual(Unlocalize::date('0000-00-00 00:00:00'), null);
	}

	/**
	 * testLocalizedDate
	 *
	 * @retun void
	 * @access public
	 */
	public function testLocalizedDate()
	{
		Unlocalize::setLocale('pt_BR');

		$this->assertEqual(Unlocalize::date('2009-04-21'), '2009-04-21');
		$this->assertEqual(Unlocalize::date('1987-03-01'), '1987-03-01');
	}

	/**
	 * testBrDate
	 *
	 * @retun void
	 * @access public
	 */
	public function testBrDate()
	{
		Unlocalize::setLocale('pt_BR');

		$this->assertEqual(Unlocalize::date('21/04/2009'), '2009-04-21');
		$this->assertEqual(Unlocalize::date('21/4/2009'), '2009-04-21');

		$this->assertEqual(Unlocalize::date('01/03/1987'), '1987-03-01');
		$this->assertEqual(Unlocalize::date('1/3/1987'), '1987-03-01');
	}

	/**
	 * testBrDateTime
	 *
	 * @retun void
	 * @access public
	 */
	public function testBrDateTime()
	{
		Unlocalize::setLocale('pt_BR');

		$this->assertEqual(Unlocalize::date('21/04/2009 12:03:01', true), '2009-04-21 12:03:01');
		$this->assertEqual(Unlocalize::date('21/4/2009 23:59:59', true), '2009-04-21 23:59:59');

		$this->assertEqual(Unlocalize::date('01/03/1987 12:03:01', true), '1987-03-01 12:03:01');
		$this->assertEqual(Unlocalize::date('1/3/1987 23:59:59', true), '1987-03-01 23:59:59');

		$this->assertEqual(Unlocalize::date('1/3/1987 23:59', true), '1987-03-01 23:59:00');
	}

	public function testUsDate()
	{
		Unlocalize::setLocale('en_US');

		$this->assertEqual(Unlocalize::date('2009-04-21'), '2009-04-21');
		$this->assertEqual(Unlocalize::date('1987-03-01'), '1987-03-01');
	}

	public function testBrDecimals()
	{
		Unlocalize::setLocale('pt_BR');

		$this->assertEqual(Unlocalize::decimal(null), null);
		$this->assertEqual(Unlocalize::decimal(''), '');
		$this->assertEqual(Unlocalize::decimal(23.32), '23.32');
		$this->assertEqual(Unlocalize::decimal('25,32'), '25.32');
		$this->assertEqual(Unlocalize::decimal('0,5'), '0.5');
		$this->assertEqual(Unlocalize::decimal('1.300,52'), '1300.52');
		$this->assertEqual(Unlocalize::decimal('3.965.300,52'), '3965300.52');

		// Invalid decimal
		$this->assertEqual(Unlocalize::decimal('3,abc'), '3.abc');
	}

	public function testUsDecimals()
	{
		Unlocalize::setLocale('en_US');

		$this->assertEqual(Unlocalize::decimal(null), null);
		$this->assertEqual(Unlocalize::decimal(''), '');
		$this->assertEqual(Unlocalize::decimal(23.32), '23.32');
		$this->assertEqual(Unlocalize::decimal('25.32'), '25.32');
		$this->assertEqual(Unlocalize::decimal('0.5'), '0.5');
		$this->assertEqual(Unlocalize::decimal('1,300.52'), '1300.52');
		$this->assertEqual(Unlocalize::decimal('3,965,300.52'), '3965300.52');

		// Invalid decimal
		$this->assertEqual(Unlocalize::decimal('3.abc'), '3.abc');
	}
}

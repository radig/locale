<?php
/**
 * Test for Unlocalized Lib
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2011, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

App::import('Lib', 'Locale.Unlocalize');
class UnlocalizeCase extends CakeTestCase
{
	/**
	 * setUp
	 *
	 * @retun void
	 * @access public
	 */
	public function startCase()
	{
		parent::startCase();
	}

	/**
	 * testAddFormat
	 *
	 * @return void
	 */
	public function testAddFormat()
	{
		$this->assertEqual(Unlocalize::addFormat('es', array()), Unlocalize::getInstance());
	}

	/**
	 * testisISODate
	 *
	 * @return void
	 */
	public function testIsISODate()
	{
		$this->assertFalse(Unlocalize::isISODate('1987-3-1'));
		$this->assertFalse(Unlocalize::isISODate('87-3-1'));
		$this->assertFalse(Unlocalize::isISODate('1987/03/01'));
		$this->assertFalse(Unlocalize::isISODate('01/03/1987'));
		$this->assertFalse(Unlocalize::isISODate('1987-23-01'));
		$this->assertFalse(Unlocalize::isISODate('1987-03-01 04:052:211'));

		$this->assertTrue(Unlocalize::isISODate('1987-03-01'));
		$this->assertTrue(Unlocalize::isISODate('1987-03-01 04:05:21'));
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
		Unlocalize::setLocale('br');

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
		Unlocalize::setLocale('br');

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
		Unlocalize::setLocale('br');

		$this->assertEqual(Unlocalize::date('21/04/2009'), '2009-04-21');
		$this->assertEqual(Unlocalize::date('21/4/2009'), '2009-04-21');

		$this->assertEqual(Unlocalize::date('01/03/1987'), '1987-03-01');
		$this->assertEqual(Unlocalize::date('1/3/1987'), '1987-03-01');
	}

	public function testUsDate()
	{
		Unlocalize::setLocale('us');

		$this->assertEqual(Unlocalize::date('2009-04-21'), '2009-04-21');
		$this->assertEqual(Unlocalize::date('1987-03-01'), '1987-03-01');
	}
}

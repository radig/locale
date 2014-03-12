<?php
App::uses('Utils', 'Locale.Lib');
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
class UtilsCase extends CakeTestCase
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

		// data input always in en_US
		setlocale(LC_ALL, 'en_US');
	}

	/**
	 * testisISODate
	 *
	 * @return void
	 */
	public function testIsISODate()
	{
		$this->assertFalse(Utils::isISODate('1987-3-1'));
		$this->assertFalse(Utils::isISODate('87-3-1'));
		$this->assertFalse(Utils::isISODate('1987/03/01'));
		$this->assertFalse(Utils::isISODate('01/03/1987'));
		$this->assertFalse(Utils::isISODate('1987-23-01'));
		$this->assertFalse(Utils::isISODate('1987-03-01 04:052:211'));

		$this->assertTrue(Utils::isISODate('1987-03-01'));
		$this->assertTrue(Utils::isISODate('1987-03-01 04:05:21'));
	}

	public function testIsNullDate()
	{
		$this->assertFalse(Utils::isNullDate('1987-3-1'));
		$this->assertFalse(Utils::isNullDate('87-3-1'));
		$this->assertFalse(Utils::isNullDate('1987-03-01'));

		$this->assertTrue(Utils::isNullDate(null));
		$this->assertTrue(Utils::isNullDate(false));
		$this->assertTrue(Utils::isNullDate(''));
		$this->assertTrue(Utils::isNullDate('0000-00-00'));
		$this->assertTrue(Utils::isNullDate('0000-00-00 04:05:21'));
		$this->assertTrue(Utils::isNullDate('0000-00-00 00:00:00'));
	}

	public function testInitDateTime()
	{
		$this->assertEqual(Utils::initDateTime(''), new DateTime());
		$this->assertEqual(Utils::initDateTime(null), new DateTime());
		$this->assertEqual(Utils::initDateTime('0000-00-00'), new DateTime());
		$this->assertEqual(Utils::initDateTime('0000-00-00 00:00:00'), new DateTime());

		$this->assertEqual(Utils::initDateTime('01/03/1987'), new DateTime('1987-01-03'));
		$this->assertEqual(Utils::initDateTime('30/03/1987'), new DateTime());

		$this->assertEqual(Utils::initDateTime('1987-03-01'), new DateTime('1987-03-01'));
		$this->assertEqual(Utils::initDateTime('2012-04-16'), new DateTime('2012-04-16'));
		$this->assertEqual(Utils::initDateTime('2012-04-16 09:37:45'), new DateTime('2012-04-16 09:37:45'));
	}

	public function testNumberFormat()
	{
		$this->assertEqual(Utils::numberFormat(1), '1.00');
		$this->assertEqual(Utils::numberFormat('1.5'), '1.50');
		$this->assertEqual(Utils::numberFormat(1.5), '1.50');
		$this->assertEqual(Utils::numberFormat(1.534), '1.53');

		$this->assertEqual(Utils::numberFormat(1.534, 3), '1.534');

		$this->assertEqual(Utils::numberFormat('1,234.56'), '1234.56');
		$this->assertEqual(Utils::numberFormat('1,234.56', null, true), '1,234.56');

		$this->assertEqual(Utils::numberFormat('0.5', 2, true, '.', ',', true), '0.50');
	}

	public function testLocalizedNumberFormat()
	{
		setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'pt-br', 'portuguese');
		$val = 12.21;

		$this->assertEqual((string)$val, '12,21');
		$this->assertEqual(Utils::numberFormat($val), '12.21');
	}

	public function testParseModelField()
	{
		$this->assertEqual(Utils::parseModelField('ModelName.field'), array('ModelName', 'field'));
		$this->assertEqual(Utils::parseModelField('Model0Name.field'), array('Model0Name', 'field'));
		$this->assertEqual(Utils::parseModelField('ModelName.field <'), array('ModelName', 'field'));
		$this->assertEqual(Utils::parseModelField('ModelName.field >='), array('ModelName', 'field'));
		$this->assertEqual(Utils::parseModelField('ModelName.field %'), array('ModelName', 'field'));

		$this->assertEqual(Utils::parseModelField('field'), array('', 'field'));
		$this->assertEqual(Utils::parseModelField('field <'), array('', 'field'));
		$this->assertEqual(Utils::parseModelField('field >='), array('', 'field'));
		$this->assertEqual(Utils::parseModelField('field %'), array('', 'field'));

		// Invalid formats
		$this->assertEqual(Utils::parseModelField('Model_Name.field'), array('', 'Model'));
	}
}

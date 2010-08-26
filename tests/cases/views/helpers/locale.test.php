<?php
/**
 * Testes do Helper Locale
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @author        Cauan Cabral <cauan@radig.com.br>, José Agripino <jose@radig.com.br>
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
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
	public function setUp()
	{
		parent::setUp();

		Configure::write('Config.language', 'pt-br');

		$this->Locale = new LocaleHelper();
	}

	/**
	 * testData
	 *
	 * @retun void
	 * @access public
	 */
	function testDate()
	{
		$this->assertEqual($this->Locale->date(), date('d/m/Y'));
		$this->assertEqual($this->Locale->date('2009-04-21'), '21/04/2009');
		$this->assertEqual($this->Locale->date('invalido'), date('invalido'));
	}

	/**
	 * testDataHora
	 *
	 * @retun void
	 * @access public
	 */
	function testDateTime()
	{
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40'), '26/08/2010 16:12:40');
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40', false), '26/08/2010 16:12');
	}

	/**
	 * testDataCompleta
	 *
	 * @retun void
	 * @access public
	 */
	function testDateLiteral()
	{
		$this->assertEqual($this->Locale->dateLiteral('2010-08-26 16:12:00'), 'Quinta-feira, 26 de Agosto de 2010, 16:12:00');
	}

	/**
	 * testPrecisao
	 *
	 * @retun void
	 * @access public
	 *
	function testPrecisao() {
		$this->assertIdentical($this->Formatacao->precisao(-10), '-10,000');
		$this->assertIdentical($this->Formatacao->precisao(0), '0,000');
		$this->assertIdentical($this->Formatacao->precisao(10), '10,000');
		$this->assertIdentical($this->Formatacao->precisao(10.323), '10,323');
		$this->assertIdentical($this->Formatacao->precisao(10.56486), '10,565');
		$this->assertIdentical($this->Formatacao->precisao(10.56486, 2), '10,56');
		$this->assertIdentical($this->Formatacao->precisao(10.56486, 0), '11');
	}

	/**
	 * testMoeda
	 *
	 * @retun void
	 * @access public
	 *
	function testMoeda() {
		$this->assertEqual($this->Formatacao->moeda(-10), '(R$ 10,00)');
		$this->assertEqual($this->Formatacao->moeda(-10.12), '(R$ 10,12)');
		$this->assertEqual($this->Formatacao->moeda(-0.12), '(R$ 0,12)');
		$this->assertEqual($this->Formatacao->moeda(-0.12, array('negative' => '-')), 'R$ -0,12');
		$this->assertEqual($this->Formatacao->moeda(0), 'R$ 0,00');
		$this->assertEqual($this->Formatacao->moeda(0.5), 'R$ 0,50');
		$this->assertEqual($this->Formatacao->moeda(0.52), 'R$ 0,52');
		$this->assertEqual($this->Formatacao->moeda(10), 'R$ 10,00');
		$this->assertEqual($this->Formatacao->moeda(10.12), 'R$ 10,12');
	}
	 * 
	 */
}

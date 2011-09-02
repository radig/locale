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
	public function startCase()
	{
		parent::startCase();

		Configure::write('Language.default', 'pt-br');
		setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'pt-br', 'pt_BR.iso-8859-1');

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
		$this->assertEqual($this->Locale->date(), date('d/m/Y'));
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
		$this->assertEqual($this->Locale->date('0000-00-00'), date('d/m/Y'));
		$this->assertEqual($this->Locale->date('0000-00-00', true), '');
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
		$this->assertEqual($this->Locale->dateTime('0000-00-00 00:00:00', false), date('d/m/Y H:i'));
		$this->assertEqual($this->Locale->dateTime('0000-00-00 00:00:00', false, true), '');
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
		
		$dateTime = new DateTime();
		$this->assertEqual($this->Locale->dateLiteral('0000-00-00 00:00:00', false), strftime('%A, %e de %B de %Y', $dateTime->format('U')));
		$this->assertEqual($this->Locale->dateLiteral('0000-00-00 00:00:00', false, null, true), '');
	}

	public function testCurrency()
	{
		$this->assertEqual($this->Locale->currency('12.45'), 'R$ 12,45');
		
		$this->assertEqual($this->Locale->currency('1,234.45'), 'R$ 1.234,45');
		
		$this->assertEqual($this->Locale->currency('1,234,567.45'), 'R$ 1.234.567,45');

		$this->assertEqual($this->Locale->currency('-'), '-');
	}

	public function testNumber()
	{
		$this->assertEqual($this->Locale->number('12'), '12,00'); // teste de inteiro, esperando real

		$this->assertEqual($this->Locale->number('12', 0), '12'); // teste de inteiro

		$this->assertEqual($this->Locale->number('12.45'), '12,45'); // teste de real

		$this->assertEqual($this->Locale->number('12.82319', 4), '12,8231'); // teste de real com precisão 4

		$this->assertEqual($this->Locale->number('350020.123', 4, true), '350.020,1230'); // teste de real com separador de milhar

		$this->assertEqual($this->Locale->number('-'), 0); // teste de um número inválido
	}

	/**
	 * testLocaleWithParameter
	 *
	 * @retun void
	 * @access public
	 */
	public function testLocaleWithParameter()
	{
		$this->Locale = new LocaleHelper(array('locale' => 'br', 'numbers' => array('decimal_point' => '!')));

		$this->assertEqual($this->Locale->date(), date('d/m/Y'));
		$this->assertEqual($this->Locale->date('2009-04-21'), '21/04/2009');
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40'), '26/08/2010 16:12:40');
		$this->assertEqual($this->Locale->dateTime('2010-08-26 16:12:40', false), '26/08/2010 16:12');
		$this->assertEqual($this->Locale->dateLiteral('2010-08-26 16:12:40'), 'quinta, 26 de agosto de 2010');
		$this->assertEqual($this->Locale->dateLiteral('2010-08-26 16:12:40', true), 'quinta, 26 de agosto de 2010, 16:12:40');

		$this->assertEqual($this->Locale->number('12.53'), '12!53');
	}
}

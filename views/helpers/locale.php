<?php
/** 
 * Helper to localized formatting dates, numbers and currency from databases format.
 * 
 * Based on Juan Basso cake_ptbr plugin: http://github.com/jrbasso/cake_ptbr
 * 
 * Code comments in brazilian portuguese.
 * -----
 * Helper para formatação localizada de datas, números decimais e valores monetários.
 * 
 * Baseado no plugin cake_ptbr do Juan Basso: http://github.com/jrbasso/cake_ptbr
 * 
 * PHP version > 5.2.6
 * 
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @copyright 2009-2011, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package radig
 * @subpackage radig.l10n.views.helpers
 */

class LocaleHelper extends AppHelper
{
	/**
	 * Lista de formatos padrões para datas de acordo com locale
	 * 
	 * @var array
	 */
	protected $_dateFormats = array(
		'us' => array('small' => 'Y-m-d', 'literal' => '%a %d %b %Y', 'literalWithTime' => '%a %d %b %Y %T', 'full' => 'Y-m-d H:i:s'),
		'br' => array('small' => 'd/m/Y', 'literal' => '%A, %e de %B de %Y', 'literalWithTime' => '%A, %e de %B de %Y, %T', 'full' => 'd/m/Y H:i:s')
	);

	/**
	 * Configurações que sobreescrevem as definições do locale
	 *
	 * @var array
	 */
	protected $_settings = array(
		'locale' => null,
		'numbers' => array(),
		'dates' => array()
	);
	
	public function __construct()
	{
		parent::__construct();

		// caso tenha sido passado um parâmetro
		if(func_num_args() == 1)
		{
			$settings = func_get_arg(0);
		}

		// recupera lista de configuração definida como argumento do helper
		if(isset($settings) && is_array($settings))
		{
			$this->_settings = array_merge($this->_settings, $settings);
		}

		// se não tiver sido passado o locale desejado, busca o locale na configuração do Cake
		if(empty($this->_settings['locale']))
		{
			$this->_settings['locale'] = substr(Configure::read('Language.default'), -2);
		}

		// mescla configurações passadas com configurações de numeração para o locale atual
		$this->_settings['numbers'] = array_merge(localeconv(), $this->_settings['numbers']);

		// mescla configurações passadas com configurações de datas para o locale atual
		$this->_settings['dates'] = array_merge($this->_dateFormats[$this->_settings['locale']], $this->_settings['dates']);
	}
	
	/* Datas */
	
	/**
	 * 
	 * @param string $d - Uma data
	 * @param bool $empty - Se deve retornar valor vazio caso uma data não seja fornecida
	 */
	public function date($d = null, $empty = false)
	{
		// caso não tenha sido passado uma data e o retorno deva ser vazio, apenas retorna
		if($this->__isNullDate($d) && $empty === true)
		{
			return '';
		}
		
		$d = $this->__adjustDateTime($d);
		
		return $d->format($this->_settings['dates']['small']);
	}

	/**
	 * 
	 * @param string $dateTime
	 * @param bool $seconds
	 * @param bool $empty
	 */
	public function dateTime($dateTime = null, $seconds = true, $empty = false)
	{
		if( $this->__isNullDate($dateTime) && $empty === true)
		{
			return '';
		}
		
		$dateTime = $this->__adjustDateTime($dateTime);
		
		$format = $this->_settings['dates']['full'];
		
		if ($seconds !== true)
		{
			// considera que último caracter  do formato representa os segundos
			$format = substr($format, 0, -2);
		}
		
		return $dateTime->format($format);
	}

	/**
	 * 
	 * @param string $dateTime
	 * @param string $displayTime
	 * @param string $format
	 * @param bool $empty
	 */
	public function dateLiteral($dateTime = null, $displayTime = false, $format = null, $empty = false)
	{
		if($this->__isNullDate($dateTime) && $empty === true)
		{
			return '';
		}
		
		$dateTime = $this->__adjustDateTime($dateTime);

		if($format == null)
		{
			if($displayTime)
			{
				$format = $this->_settings['dates']['literalWithTime'];
			}
			else
			{
				$format = $this->_settings['dates']['literal'];
			}
		}

		return strftime($format, $dateTime->format('U'));
	}

	/**
	 *
	 * @param number $value
	 * @return string
	 */
	public function currency($value)
	{
		// guarda o locale atual para restauração posterior
		$curLocale = setlocale(LC_NUMERIC, "0");
		
		// garante que o separador de decimal será o ponto (dot) enquanto separador de milhar será vírgula (period)
		setlocale(LC_NUMERIC, 'en_US');
		
		// remove o separador de milhar (se houver)
		$value = str_replace(',', '', $value);
		
		if(empty($value) || !is_numeric($value))
		{
			return $value;
		}
		
		$currency = money_format("%.2n", $value);
		
		// restaura locale anterior
		setlocale(LC_NUMERIC, $curLocale);

		return $currency;
	}

	/**
	 *
	 * @param number $value
	 * @param int $precision
	 * @param boolean $thousands
	 * @return number
	 */
	public function number($value, $precision = 2, $thousands = false)
	{
		if(is_numeric($value))
		{
			$value = $this->__number_format($value, $precision, $thousands);
		}
		
		return $value;
	}
	
	/** Métodos para uso interno **/

	/**
	 * Retorna true caso a data passada represente um valor nulo
	 * - Formato da data é dependente do BD utilizado
	 * 
	 * @param string $d
	 */
	protected function __isNullDate($d)
	{
		// Empty | null
		if(empty($d))
		{
			return true;
		}
		
		// MySQL null date format
		if(is_int(strpos($d, '0000-00-00')))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Recebe uma string de data e retorna um objeto DateTime para a data
	 *
	 * @param string $d
	 * @return DateTime
	 */
	protected function __adjustDateTime($d)
	{
		if ($this->__isNullDate($d))
		{
			return new DateTime();
		}

		try{
			$dt = new DateTime($d);
		}
		catch(Exception $e)
		{
			$dt = new DateTime();
		}
		
		return $dt;
	}

	/**
	 * Função replacement para number_format, com alguns recursos extras:
	 *  - Não faz arredondamento do número (utiliza truncamento)
	 *  - Suporta, opcionalmente, separador de milhar
	 *
	 * @param numeric $value
	 * @param int $precision
	 * @param boolean $thousands
	 *
	 * @return numeric
	 */
	private function __number_format($value, $precision, $thousands)
	{
		// guarda o locale atual para restauração posterior
		$curLocale = setlocale(LC_NUMERIC, "0");

		// garante que o separador de decimal será o ponto (dot)
		setlocale(LC_NUMERIC, 'en_US');

		// remove o separador de milhar (se houver)
		$value = str_replace(',', '', $value);
		
		// separa a parte decimal
		$parts = explode('.', $value);

		// caso o número possua parte decimal
		if(count($parts) == 2)
		{
			$int = (string)$parts[0];
			$dec = str_pad((string)$parts[1], $precision, '0', STR_PAD_RIGHT);
		}
		// caso não possua
		else
		{
			$int = (string)$parts[0];
			$dec = str_repeat('0', $precision);
		}

		// trunca o número
		$dec = substr($dec, 0, $precision);

		// caso requerido, insere separador de milhar
		if($thousands)
		{
			$int = number_format($int, 0, $this->_settings['numbers']['decimal_point'], '.');
		}

		// caso posssua decimais, faz a junção usando separador localizado
		if(!empty($dec))
		{
			$number = $int . $this->_settings['numbers']['decimal_point'] . $dec;
		}
		// caso contrário o número é um inteiro
		else
		{
			$number = $int;
		}

		// restaura locale anterior
		setlocale(LC_NUMERIC, $curLocale);

		// retorna número resultante
		return $number;
	}
}
?>

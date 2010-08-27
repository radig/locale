<?php
/**
 * Helper para formatação de dados localizados (de acordo com o locale setado)
 * Requer PHP 5.2.6 ou superior
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @author        Juan Basso <jrbasso@gmail.com> - Plugin Cake_ptbr
 * @author        Cauan Cabral <cauan@radig.com.br> - Generalização do Locale, adaptação para Cake 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class LocaleHelper extends AppHelper
{
	private $currentLocale;
	
	protected $_dateFormats = array(
		'us' => array('small' => 'Y-m-d', 'literal' => '%a %d %b %Y', 'literalWithTime' => '%a %d %b %Y %T', 'full' => 'Y-m-d H:i:s'),
		'br' => array('small' => 'd/m/Y', 'literal' => '%A, %e de %B de %Y', 'literalWithTime' => '%A, %e de %B de %Y, %T', 'full' => 'd/m/Y H:i:s')
	);

	protected $_numberFormats;
	
	public function __construct( $locale = null )
	{
		if($locale == null)
		{
			$this->currentLocale = substr(Configure::read('Config.language'), -2);
		}
		else
		{
			$this->currentLocale = $locale;
		}

		$this->_numberFormats = localeconv();
		
		parent::__construct();
	}
	
	/* Datas */
	
	public function date($d = null)
	{
		$d = $this->__adjustDateTime($d);
		
		return $d->format($this->_dateFormats[$this->currentLocale]['small']);
	}

	public function dateTime($dateTime = null, $seconds = true)
	{
		$dateTime = $this->__adjustDateTime($dateTime);
		
		$format = $this->_dateFormats[$this->currentLocale]['full'];
		
		if ($seconds !== true)
		{
			// considera que último caracter  do formato representa os segundos
			$format = substr($format, 0, -2);
		}
		
		return $dateTime->format($format);
	}

	public function dateLiteral($dateTime = null, $displayTime = false, $format = null)
	{
		$dateTime = $this->__adjustDateTime($dateTime);

		if($format == null)
		{
			if($displayTime)
			{
				$format = $this->_dateFormats[$this->currentLocale]['literalWithTime'];
			}
			else
			{
				$format = $this->_dateFormats[$this->currentLocale]['literal'];
			}
		}

		return strftime($format, $dateTime->format('U'));
	}

	public function __adjustDateTime($d)
	{
		if ($d === null)
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

	public function currency($value)
	{
		if(!empty($value) && is_numeric($value))
		{
			$currency = money_format("%.2n",$value);

			return $currency;
		}
		else
		{
			trigger_error(sprintf(__('Falha ao converter o valor monetário "%s"', true), $value), E_USER_NOTICE);
			
			return '';
		}
	}

	public function number($value, $precision = 2, $thousands = false)
	{
		if(!empty($value) && is_numeric($value))
		{
			$value = $this->number_format($value, $precision, $thousands);
			
			return $value;
		}
		else
		{
			trigger_error(sprintf(__('Falha ao converter o número "%s"', true), $value), E_USER_NOTICE);
			
			return 0;
		}
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
	private function number_format($value, $precision, $thousands)
	{
		// remove o separador de milhar (se houver)
		$value = str_replace(',', '', $value);
		
		// separa a parte decimal
		$parts = explode('.', $value);
		$int = (string)$parts[0];
		$dec = (string)$parts[1];

		// trunca o número
		$dec = substr($dec, 0, $precision);

		// caso requerido, insere separador de milhar
		if($thousands)
		{
			$v = '';

			for($i = (strlen($int) - 1); $i >= 0; --$i)
			{
				$v = $int[$i] . $v;
				
				if($i%3 == 0 && $i != 0)
				{
					$v .= $this->_numberFormats['thousands_sep'];
				}
			}

			$int = $v;
		}

		// atualizo $parts
		$parts[0] = $int;
		$parts[1] = $dec;

		// retorna os valores unidos pelo separador de decimal localizado
		return implode($this->_numberFormats['decimal_point'], $parts);
	}
}
?>
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
			$this->_settings['locale'] = substr(Configure::read('Config.language'), -2);
		}

		// mescla configurações passadas com configurações de numeração para o locale atual
		$this->_settings['numbers'] = array_merge(localeconv(), $this->_settings['numbers']);

		// mescla configurações passadas com configurações de datas para o locale atual
		$this->_settings['dates'] = array_merge($this->_dateFormats[$this->_settings['locale']], $this->_settings['dates']);
	}
	
	/* Datas */
	
	public function date($d = null)
	{
		$d = $this->__adjustDateTime($d);
		
		return $d->format($this->_settings['dates']['small']);
	}

	public function dateTime($dateTime = null, $seconds = true)
	{
		$dateTime = $this->__adjustDateTime($dateTime);
		
		$format = $this->_settings['dates']['full'];
		
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

	/**
	 *
	 * @param number $value
	 * @param int $precision
	 * @param boolean $thousands
	 * @return number
	 */
	public function number($value, $precision = 2, $thousands = false)
	{
		if(!empty($value) && is_numeric($value))
		{
			$value = $this->__number_format($value, $precision, $thousands);
			
			return $value;
		}
		else
		{
			trigger_error(sprintf(__('Falha ao converter o número "%s"', true), $value), E_USER_NOTICE);
			
			return 0;
		}
	}
	
	/** Métodos para uso interno **/

	/**
	 * Recebe uma string de data e retorna um objeto DateTime para a data
	 *
	 * @param string $d
	 * @return DateTime
	 */
	protected function __adjustDateTime($d)
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
					$v .= $this->_settings['numbers']['thousands_sep'];
				}
			}

			$int = $v;
		}

		// atualizo $parts
		$parts[0] = $int;
		$parts[1] = $dec;

		// retorna os valores unidos pelo separador de decimal localizado
		return implode($this->_settings['numbers']['decimal_point'], $parts);
	}
}
?>
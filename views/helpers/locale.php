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
		
		parent::__construct();
	}
	
	/* Datas */
	
	public function date($d = null)
	{
		$d = $this->__adjustDateTime($d);
		
		return $d->format($this->_dateFormats[$this->currentLocale]['small']);
	}

	function dateTime($dateTime = null, $seconds = true)
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

	function dateLiteral($dateTime = null, $displayTime = false, $format = null)
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

	function __adjustDateTime($d)
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
}
?>
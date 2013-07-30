<?php 

	/**
	 * Classe de exce��o
	 * 
	 * @author Pablo Santiago S�nchez <phackwer@gmail.com>
	 * @version 1.0.0
	 * @package SanSIS_Wfm
	 * @subpackage Exception
	 *
	 */

	class SanSIS_Wfm_Exception_ConfigAlreadyExists extends SanSIS_Wfm_Exception_Exception
	{
		protected $message = 'J� existe uma configura��o com este nome.';
	}

?>
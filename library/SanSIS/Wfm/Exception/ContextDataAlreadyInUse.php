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

	class SanSIS_Wfm_Exception_ContextDataAlreadyInUse extends SanSIS_Wfm_Exception_Exception
	{
		protected $message = 'Contexto j� est� em uso, n�o pode ser substitu�do.'; 
	}

?>
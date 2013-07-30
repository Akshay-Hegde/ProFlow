<?php 
	
	/**
	 * Classe respons�vel por conter a interpreta��o da Transi��o de Atividade
	 * do XPDL para utiliza��o no contexto do processo em execu��o
	 * 
	 * @author Pablo Santiago S�nchez <phackwer@gmail.com>
	 * @version 1.0.0
	 * @package SanSIS_Wfm
	 * @subpackage Engine
	 *
	 */
	class SanSIS_Wfm_Engine_ContextTransition extends SanSIS_Wfm_Base
	{
		//atributos requeridos
		private $process;					//Processo ao qual a Transi��o pertence
		private $id;						//id
		private $from;						//Atividade de origem da Transi��o
		private $to;						//Atividade de destino da Transi��o		
		
		/**
		 * Defini��o XPDL da transi��o j� carregada em um DOMElement
		 * @param DOMElement $processNode
		 */
		public function __construct(DOMElement $processNode, SanSIS_Wfm_Engine_ContextProcess $process)
		{
			$this->process			= $process;
			$this->id				= $processNode->getAttribute('Id');
			$this->from				= $processNode->getAttribute('From');
			$this->to				= $processNode->getAttribute('To');
			
			$this->xpdlDefinition	= simplexml_import_dom($processNode)->asXML();
		}
		
		/**
		 * Obt�m id da Transi��o
		 * @return string
		 */
		public function getId()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Id da Transi��o "'.$this->id.'".');
			
			return $this->id;
		}
		
		/**
		 * Obt�m origem da Transi��o
		 * @return string
		 */
		public function getFrom()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Id da Atividade origem da Transi��o "'.$this->id.'".');
			
			return $this->from;
		}
		
		/**
		 * Obt�m Atividade de origem da Transi��o
		 * @return WfContextActivity
		 */
		public function getActivityFrom()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Atividade origem da Transi��o "'.$this->id.'".');
			
			return $this->process->getActivity($this->from);
		}
		
		/**
		 * Obt�m destino da Transi��o
		 * @return string
		 */
		public function getTo()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Id da Atividade destino da Transi��o "'.$this->id.'".');
			
			return $this->to;
		}
		
		/**
		 * Obt�m Atividade de destino da Transi��o
		 * @return WfContextActivity
		 */
		public function getActivityTo()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Atividade destino da Transi��o "'.$this->id.'".');
			
			return $this->process->getActivity($this->to);
		}
		
		/**
		 * Obt�m Processo que cont�m a Transi��o
		 * @return WfContextProcess
		 */
		public function getProcess()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Processo da Transi��o "'.$this->id.'".');
			
			return $this->process;
		}
		
		/**
		 * Obt�m a defini��o XPDL da Transi��o
		 * @return string
		 */
		public function getXPDLDefinition()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo XPDL da Transi��o "'.$this->id.'".');
			
			return $this->xpdlDefinition;
		}
	}
	
?>
<?php 
	
	/**
	 * Classe respons�vel por conter a interpreta��o do Participante
	 * do XPDL para utiliza��o no contexto do processo em execu��o
	 * 
	 * @author Pablo Santiago S�nchez <phackwer@gmail.com>
	 * @version 1.0.0
	 * @package SanSIS_Wfm
	 * @subpackage Engine
	 *
	 */
	class SanSIS_Wfm_Engine_ContextParticipant extends SanSIS_Wfm_Base
	{
		//atributos requeridos
		private $context;					//Contexto ao qual o Participante pertence
		private $id;						//id do Participante
		private $name;						//nome do Participante
		private $type;						//tipo do Participante
		private $processes		= array();	//Processos nos quais est� presente
		
		/**
		 * Constructor
		 * @param DOMElement $processNode - Defini��o XPDL do Processo j� carregada em um DOMElement
		 */
		public function __construct(DOMElement $processNode, SanSIS_Wfm_Engine_ContextData $context)
		{			
			$this->context			= $context;
			$this->id				= $processNode->getAttribute('Id');
			$this->name				= $processNode->getAttribute('Name');
			$this->type				= $processNode->getElementsByTagName('ParticipantType')
												  ->item(0)->getAttribute('Type');
												  
			SanSIS_Wfm_Debug_Debug::info('Mapeando Participante "'.$this->id.'" - "'.$this->name.'".');
			
			$this->xpdlDefinition	= simplexml_import_dom($processNode)->asXML();
			
			SanSIS_Wfm_Debug_Debug::log('Participante "'.$this->id.'" - "'.$this->name.'" mapeado.');
		}
		
		/**
		 * Adiciona um Processo associado ao Participante
		 * @param WfContextProcess $process
		 * @return void
		 */
		public function addProcess(SanSIS_Wfm_Engine_ContextProcess $process)
		{
			$id = $process->getId();
			$this->processes[$id] = $process;
			
			SanSIS_Wfm_Debug_Debug::log('Processo "'.$id.'" associado ao Participante.');
		}
		
		/**
		 * Obt�m id do Participante
		 * @return string
		 */
		public function getId()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Id do Participante "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->id;
		}
		
		/**
		 * Obt�m nome do Participante
		 * @return string
		 */
		public function getName()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Nome do Participante "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->name;
		}
		
		/**
		 * Obt�m tipo do Participante
		 * @return string
		 */
		public function getType()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Tipo do Participante "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->type;
		}
		
		/**
		 * Obt�m Contexto que cont�m o Participante
		 * @return WfContextData
		 */
		public function getContext()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Contexto do Participante "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->context;
		}
		
		/**
		 * Obt�m os Processos aos quais o Participante est� associado
		 * @return array WfContextProcess
		 */
		public function getProcesses()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Processos vinculados ao Participante "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->processes;
		}
		
		/**
		 * Obt�m a defini��o XPDL do Participante
		 * @return string
		 */
		public function getXPDLDefinition()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo XPDL do Participante "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->xpdlDefinition;
		}
	}
	
?>
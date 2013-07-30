<?php 
	
	/**
	 * Classe respons�vel por definir as Associa��es
	 * do XPDL para utiliza��o no contexto do processo em execu��o
	 * 
	 * @author Pablo Santiago S�nchez <phackwer@gmail.com>
	 * @version 1.0.0
	 * @package SanSIS_Wfm
	 * @subpackage Engine
	 *
	 */
	class SanSIS_Wfm_Engine_ContextAssociation extends SanSIS_Wfm_Base
	{
		//atributos requeridos
		private $context;				//Contexto ao qual a Associa��o pertence
		private $id;					//id
		private $name;					//nome
		private $source;				//origem
		private $target;				//destino
		
		/**
		 * Construtor
		 * @param DOMElement $processNode
		 * @param string $type
		 * @param string $logic
		 */
		public function __construct(DOMElement $processNode, SanSIS_Wfm_Engine_ContextData $context)
		{
			SanSIS_Wfm_Debug_Debug::info('Mapeando Associa��o.');
			
			$this->context			= $context;
			$this->id				= $processNode->getAttribute('Id');
			$this->name				= $processNode->getAttribute('Name');
			
			//busca atividade, objecto de dados ou associa��o como origem da associa��o
			$this->setSource($this->context->getElementById($processNode->getAttribute('Source')));
			//busca atividade, objecto de dados ou associa��o como destino da associa��o
			$this->setTarget($this->context->getElementById($processNode->getAttribute('Target')))   ;
			
			$this->xpdlDefinition	= simplexml_import_dom($processNode)->asXML();
			
			SanSIS_Wfm_Debug_Debug::log('Associa��o mapeada.');
		}
		
		/**
		 * Define um objeto da Wf como source da Associa��o
		 * @param WfContextTransition $transition
		 * @return void
		 */
		public function setSource($source)
		{
			$id = $source->getId();
			$this->source = $source;
			
			SanSIS_Wfm_Debug_Debug::log('Objeto "'.$id.'" definido como source da Associa��o.');
		}

		/**
		 * Define um objeto da Wf como target da Associa��o
		 * @param WfContextTransition $transition
		 * @return void
		 */
		public function setTarget($target)
		{
			$id = $target->getId();
			$this->target = $target;
			
			SanSIS_Wfm_Debug_Debug::log('Objeto "'.$id.'" definido como target da Associa��o.');
		}
		
		/**
		 * Obt�m um objeto da Wf como source da Associa��o
		 * @return Object
		 */
		public function getSource()
		{
			SanSIS_Wfm_Debug_Debug::log('Obtendo Objeto source "'.$id.'" da Associa��o.');
			
			return $this->source = $source;
		}

		/**
		 * Obt�m um objeto da Wf como target da Associa��o
		 * @return Object
		 */
		public function getTarget()
		{			
			SanSIS_Wfm_Debug_Debug::log('Obtendo Objeto target "'.$id.'" da Associa��o.');
			
			return $this->target = $source;
		}		
		
		/**
		 * Obt�m a defini��o XPDL do Contexto
		 * @return string
		 */
		public function getXPDLDefinition()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo XPDL da Restri��o.');
			
			return $this->xpdlDefinition;
		}
	}
	
?>
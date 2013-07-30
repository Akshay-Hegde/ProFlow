<?php 
	
	/**
	 * Classe respons�vel por conter a Retri��o de Transi��o da Atividade
	 * do XPDL para utiliza��o no contexto do processo em execu��o
	 * 
	 * @author Pablo Santiago S�nchez <phackwer@gmail.com>
	 * @version 1.0.0
	 * @package SanSIS_Wfm
	 * @subpackage Engine
	 *
	 */
	class SanSIS_Wfm_Engine_ContextRestriction extends SanSIS_Wfm_Base
	{
		//atributos requeridos
		private $type;					//Split ou Join
		private $logic;					//XOR ou AND
		private $appliesTo	= array();	//Transi��es ao qual � aplicado
		
		/**
		 * Construtor
		 * @param DOMElement $processNode
		 * @param string $type
		 * @param string $logic
		 */
		public function __construct(DOMElement $processNode, $type, $logic)
		{
			SanSIS_Wfm_Debug_Debug::info('Mapeando Restri��o.');
			
			$this->type = $type;
			$this->logic = $logic;
			
			$this->xpdlDefinition	= simplexml_import_dom($processNode)->asXML();
			
			SanSIS_Wfm_Debug_Debug::log('Restri��o mapeada.');
		}

		/**
		 * Adiciona uma Transi��o � qual a Restri��o faz refer�ncia
		 * @param WfContextTransition $transition
		 * @return void
		 */
		public function addTransition(SanSIS_Wfm_Engine_ContextTransition $transition)
		{
			$id = $transition->getId();
			$this->appliesTo[$id] = $transition;
			
			SanSIS_Wfm_Debug_Debug::log('Transi��o "'.$id.'" associada � Restri��o.');
		}
		
		/**
		 * Obt�m as Transi��es �s quais a Restri��o � aplicada 
		 * @return array WfContextTransition
		 */
		public function getTransitions()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Transi��es �s quais a Restri��o � aplicada.');
			
			return $this->appliesTo;
		}
		
		/**
		 * Obt�m o Tipo da Restri��o (Split/Join)
		 * @return string
		 */
		public function getType()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Tipo da Restri��o.');
			
			return $this->type;	
		}
		
		/**
		 * Obt�m a L�gica da Restri��o (AND/XOR)
		 * @return string
		 */
		public function getLogic()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo L�gica da Restri��o.');
			
			return $this->logic;
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
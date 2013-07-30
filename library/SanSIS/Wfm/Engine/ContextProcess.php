<?php 
	
	/**
	 * Classe respons�vel por conter a interpreta��o do Processo
	 * do XPDL para utiliza��o no contexto do processo em execu��o
	 * 
	 * @author Pablo Santiago S�nchez <phackwer@gmail.com>
	 * @version 1.0.0
	 * @package SanSIS_Wfm
	 * @subpackage Engine
	 *
	 */
	class SanSIS_Wfm_Engine_ContextProcess extends SanSIS_Wfm_Base
	{
		//atributos requeridos
		private $context;					//Contexto ao qual o Processo pertence
		private $id;						//id do Processo
		private $name;						//nome do Processo
		private $version;					//vers�o/data de cria��o do registro
		private $startEvent;				//evento de in�cio
		private $endEvents		= array();	//eventos de fim (cancelamento/finaliza��o)
		private $transitions	= array();	//lista de Participantes do Processo
		private $activities		= array();	//lista de Atividades contidas no Processo
		private $participants	= array();	//lista de Participantes do Processo
		private $dataobjects	= array();	//lista de DataObjects do Processo
		private $associations	= array();	//lista de Associa��es do Processo
		
		/**
		 * Construtor
		 * @param DOMElement $processNode
		 * @param WfContextData $context
		 */
		public function __construct(DOMElement $processNode, SanSIS_Wfm_Engine_ContextData $context)
		{
			$this->context			= $context;
			$this->id				= $processNode->getAttribute('Id');
			$this->name				= $processNode->getAttribute('Name');
			if ($processNode->getElementsByTagName('Created')->item(0))
				$this->version			= $processNode->getElementsByTagName('Created')->item(0)->nodeValue;
			
			SanSIS_Wfm_Debug_Debug::info('Mapeando Processo "'.$this->id.'" - "'.$this->name.'".');
					
			$this->xpdlDefinition	= simplexml_import_dom($processNode)->asXML();
			
			//transi��es devem ser processadas ANTES das atividades
			$this->processTransitions($processNode);
			$this->processActivities($processNode);
			
			SanSIS_Wfm_Debug_Debug::log('Processo "'.$this->id.'" - "'.$this->name.'" mapeado.');
		}	
		
		/**
		 * Processa as Transi��es presentes no Processo entre as Atividades
		 * As Transi��es s�o o que definem o caminho a ser seguido pelo Processo
		 * @param DOMElement $processNode
		 * @return void
		 */
		private function processTransitions(DOMElement $processNode)
		{
			$transitions = $processNode->getElementsByTagName('Transition');
			if ($transitions->length < 1)
				throw new SanSIS_Wfm_Exception_NoTransitionsOnXPDL();
				
			foreach ($transitions as $transition)
			{				
				$id = $transition->getAttribute('Id');
				$this->transitions[$id] = new SanSIS_Wfm_Engine_ContextTransition($transition, $this);
				
				SanSIS_Wfm_Debug_Debug::log('Transi��o "'.$id.'" associada ao Processo "'.$this->id.'" - "'.$this->name.'".');
			}
		}
		
		/**
		 * Processa as Atividades presentes no Processo, e suas Restri��es de Transi��o
		 * @TODO - Tratar subfluxos nas atividades!
		 * @param DOMElement $processNode
		 * @return void
		 */
		private function processActivities(DOMElement $processNode)
		{
			$activities = $processNode->getElementsByTagName('Activity');
			if ($activities->length < 1)
				throw new SanSIS_Wfm_Exception_NoActivitiesOnXPDL();
				
			foreach ($activities as $activity)
			{
				$id = $activity->getAttribute('Id');
				$this->activities[$id] = new SanSIS_Wfm_Engine_ContextActivity($activity, $this);
				
				if ($this->activities[$id]->isStartEvent())
					$this->setStartEvent($this->activities[$id]);
					
				if ($this->activities[$id]->isEndEvent())
					$this->setEndEvents($this->activities[$id]);
				
				SanSIS_Wfm_Debug_Debug::log('Atividade "'.$id.'" associada ao Processo. "'.$this->id.'" - "'.$this->name.'".');
				
				$this->context->addActivity($this->activities[$id]);
			}
		}
		
		/**
		 * Adiciona um Participante do Contexto ao Processo
		 * @param string $id
		 * @return void
		 */
		public function addParticipant($id)
		{
			$this->participants[$id] = $this->context->getParticipant($id);
			
			SanSIS_Wfm_Debug_Debug::log('Participante "'.$id.'" associado ao Processo "'.$this->id.'" - "'.$this->name.'".');
			
			$this->participants[$id]->addProcess($this);
		}
		
		/**
		 * Obt�m um Participante espec�fico do Processo
		 * @param string $id
		 * @return WfContextParticipant
		 */
		public function getParticipant($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Participante "'.$id.'" do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			//verifica se existe participante			
			if (isset($this->participants[$id]))
				return $this->participants[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoParticipantByThatId();
		}
		
		/**
		 * Obt�m todos os Participantes do Processo
		 * @return array WfContextParticipant
		 */
		public function getParticipants()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Participantes do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->participants;
		}
		
		/**
		 * Obt�m uma Transi��o espec�fica do Processo
		 * @param string $id
		 * @return WfContextTransition
		 */
		public function getTransition($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Transi��o "'.$id.'" do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			//verifica se existe participante			
			if (isset($this->transitions[$id]))
				return $this->transitions[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoTransitionByThatId();
		}
		
		/**
		 * Obt�m todas as Transi��es do Processo
		 * @return array WfContextTransition
		 */
		public function getTransitions()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Transi��es do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->transitions;
		}
		
		/**
		 * Obt�m todas as Transi��es do Processo
		 * @return array WfContextTransition
		 */
		public function getTransitionByOrigin($fromId)
		{			
			$return = array();
			
			foreach ($this->transitions as $transition)
			{
				if ($fromId == $transition->getFrom())
					$return[] = $transition;
			}
			
			return $return;
		}
		
		/**
		 * Obt�m a primeira Atividade que inicia o processo
		 * @return WfContextActivity
		 */
		public function getStartEvent()
		{
			return $this->startEvent;
		}
		
		/**
		 * Define qual � a primeira Atividade que inicia o processo
		 * @param WfContextActivity $activity
		 * @return void
		 */
		public function setStartEvent(SanSIS_Wfm_Engine_ContextActivity $activity)
		{
			$this->startEvent = $activity;
		}
		
		/**
		 * Obt�m as Atividades que podem por fim ao processo
		 * @return WfContextActivity
		 */
		public function getEndEvents()
		{
			return $this->endEvents;
		}
		
		/**
		 * Adiciona uma Atividade que pode por fim ao processo
		 * @param WfContextActivity $activity
		 * @return void
		 */
		public function setEndEvents(SanSIS_Wfm_Engine_ContextActivity $activity)
		{
			$this->endEvents[] = $activity;
		}
		
		/**
		 * Obt�m as Atividades que podem por fim ao processo
		 * @return WfContextActivity
		 */
		public function getFinishEvents()
		{
			$finishEvents = array();
			foreach ($this->endEvents as $event)
			{
				if ($event->isFinishEvent())
					$finishEvents[] = $event;
			}
			return $finishEvents;
		}
	
		
		/**
		 * Obt�m as Atividades que podem cancelar o processo
		 * @return WfContextActivity
		 */
		public function getCancelEvents()
		{
			$cancelEvents = array();
			foreach ($this->endEvents as $event)
			{
				if ($event->isCancelEvent())
					$cancelEvents[] = $event;
			}
			return $cancelEvents;
		}
		
		/**
		 * Obt�m uma Atividade espec�fica do Processo
		 * @param string $id
		 * @return WfContextActivity
		 */
		public function getActivity($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Atividade "'.$id.'" do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			//verifica se existe participante			
			if (isset($this->activities[$id]))
				return $this->activities[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoActivityByThatId();
		}
		
		/**
		 * Obt�m todas as Atividades do Processo
		 * @return array WfContextActivity
		 */
		public function getActivities()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Atividades do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->activities;
		}
		
		/**
		 * Obt�m Id do Processo
		 * @return string
		 */
		public function getId()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Id do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->id;
		}
		
		/**
		 * Obt�m Nome do Processo
		 * @return string
		 */
		public function getName()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Nome do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->name;
		}
		
		/**
		 * Obt�m Vers�o do Processo
		 * @return string
		 */
		public function getVersion()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Vers�o do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->version;
		}
		
		/**
		 * Obt�m Contexto que cont�m o Processo
		 * @return WfContextData
		 */
		public function getContext()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Contexto do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->context;
		}
		
		/**
		 * Obt�m um Elemento qualquer por Id contidos no Contexto
		 * @param string $id
		 * @return array WfContextAssociation
		 */
		public function getElementById($id)
		{
			$element = null;
			
			try
			{
				$element = $this->getActivity($id);
			}
			catch (SanSIS_Wfm_Exception_Exception $e)
			{
				try
				{
					$element = $this->getParticipant($id);
				}
				catch (SanSIS_Wfm_Exception_Exception $e)
				{
						try
						{
							$element = $this->getTransition($id);
						}
						catch (SanSIS_Wfm_Exception_Exception $e)
						{
							throw new SanSIS_Wfm_Exception_NoElementByThatId();
						}						
				}
			}
			
			if (!$element)
				throw new SanSIS_Wfm_Exception_NoElementByThatId();
				
			
			return $element;
		}
		
		/**
		 * Obt�m a defini��o XPDL do Processo
		 * @return string
		 */
		public function getXPDLDefinition()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo XPDL do Processo "'.$this->id.'" - "'.$this->name.'".');
			
			return $this->xpdlDefinition;
		}
	}
	
?>
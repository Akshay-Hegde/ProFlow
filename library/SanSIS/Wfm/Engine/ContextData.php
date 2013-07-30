<?php 
	
	/**
	 * Classe respons�vel por interpretar o XPDL para utiliza��o
	 * no contexto do processo em execu��o
	 * 
	 * @author Pablo Santiago S�nchez <phackwer@gmail.com>
	 * @version 1.0.0
	 * @package SanSIS_Wfm
	 * @subpackage Engine
	 *
	 */
	class SanSIS_Wfm_Engine_ContextData extends SanSIS_Wfm_Base
	{
		//atributos requeridos
		private $id;						//id do Contexto
		private $name;						//nome do Contexto
		private $version;					//vers�o/data do Contexto no XPDL
		private $participants	= array();	//lista de Participantes do Contexto
		private $processes		= array();	//lista de Processos contidos no Contexto
		private $activities		= array();	//lista de Atividades contidas no Contexto
		private $dataobjects	= array();	//lista de Objetos de Dados contidos no Contexto
		private $associations	= array();	//lista de Associa��es contidas no Contexto
		
		
		/**
		 * Construtor
		 * @param string $filepath - opcional, � o caminho para o arquivo XPDL
		 */
		public function __construct($filepath = null)
		{		
			if ($filepath)
				$this->load($filepath);
		}
		
		/**
		 * Obt�m Id do contexto
		 */
		public function getId(){
			return $this->id;
		}
		
		/**
		 * Carrega fluxos definidos em um arquivo XPDL
		 * @param string $filepath
		 * @return void
		 */
		public function load($filepath)
		{
			SanSIS_Wfm_Debug_Debug::info('Criando Contexto a partir de '.$filepath.'.');
			
			if (file_exists($filepath))
			{
				if (!$this->xpdlDefinition)
				{
					$this->xpdlDefinition = file_get_contents($filepath);
					$this->processXPDL();
				}
				else
					throw new SanSIS_Wfm_Exception_ContextDataAlreadyInUse();
			}
			else
				throw new SanSIS_Wfm_Exception_NoXPDLFile();
				
			SanSIS_Wfm_Debug_Debug::log('Contexto criado.');
		}
		
		/**
		 * Carrega fluxos definidos em um arquivo XPDL, cuja refer�ncia est� em banco de dados
		 * @param string $name - nome do contexto
		 * @param integer $version - vers�o do xpdl do contexto
		 */
		public function loadFromDB($name, $status = 1, $version = null)
		{
			//primeiro, buscamos qual classe est� mapeada para o banco
			$wfcontext       = $this->getOrmClass();
			$xpdlclass       = SanSIS_Wfm_Config_Environment::getConfig()->wfxpdl->class;
			//agora os nomes das colunas de id e id do contexto e do estado
			$idfd            = $this->getMeta('id');
			$namefd          = $this->getMeta('name');
			$statusfd        = $this->getMeta('status');			
            $idxfd           = SanSIS_Wfm_Config_Environment::getConfig()->wfxpdl->id;
            $versionxfd      = SanSIS_Wfm_Config_Environment::getConfig()->wfxpdl->version;
            $statusxfd       = SanSIS_Wfm_Config_Environment::getConfig()->wfxpdl->status;
            $xpdlpathfd      = SanSIS_Wfm_Config_Environment::getConfig()->wfxpdl->xpdlPath;
			//instanciamos
            $wfxpdl          = new $xpdlclass();
            
            //carregamos o contexto
            $where           = array(
                                $wfcontext->getAdapter()->quoteInto($namefd.' = ?',     $name),
                                $wfcontext->getAdapter()->quoteInto($statusfd.' = ?',    $status)
                               );          
            $context         = $wfcontext->fetchRow($where);
            
            
            
            //agora que temos o contexto e sabemos o id dele, buscamos o arquivo xpdl
            $this->id              = $context->$idfd;
            $where           = array(
                                $wfxpdl->getAdapter()->quoteInto($idfd.' = ?', $this->id),
                                $wfxpdl->getAdapter()->quoteInto($statusxfd.' = ?', $status)
                               );
            //se n�o foi passada a vers�o, o xpdl deve ser a �ltima vers�o
            if (!$version)
            {
                $order       = $versionxfd.' desc';
            }
            else
            {
            	$where[]     = $wfxpdl->getAdapter()->quoteInto($versionxfd.' = ?', $version);
            	$order       = ''; 
            }
			
			$xpdl = $wfxpdl->fetchRow($where, $order);
			
			$this->load($xpdl->$xpdlpathfd);
		}
		
		/**
		 * Processa o XPDL do Contexto
		 * @return void
		 */
		private function processXPDL()
		{
			SanSIS_Wfm_Debug_Debug::info('Processando XPDL.');
			
			//iniciamos a string como um objeto para manipula��o
			$xpdl			= new DOMDocument();			
			$xpdl->loadXML($this->xpdlDefinition);
				
			$this->version	= $xpdl->getElementsByTagName('Created')->item(0)->nodeValue;
			
			//limpeza do XPDL Tibco
			$xpdl = $this->clearTibcoXPDL($xpdl);
			
			$this->processXPDLParticipants($xpdl);
			$this->processXPDLProcesses($xpdl);
			$this->processXPDLDataObjects($xpdl);
			$this->processXPDLAssociations($xpdl);
			
			SanSIS_Wfm_Debug_Debug::log('XPDL Processado.');
		}
		
		/**
		 * M�todo para limpar algumas sujeiras que o TIBCO cria
		 * @param DOMDocument $xpdl
		 * @return DOMDocument $xpdl
		 */
		private function clearTibcoXPDL($xpdl)
		{
			//coloca o nome correto dos elementos
			//participants
			$xpdl = $this->clearTibcoNames($xpdl, 'Participant');
			//process
			$xpdl = $this->clearTibcoNames($xpdl, 'WorkflowProcess');
			//activities
			$xpdl = $this->clearTibcoNames($xpdl, 'Activity');
			//artifacts
			$xpdl = $this->clearTibcoNames($xpdl, 'Artifact');
			
			
			//remove performers inv�lidos
			$tasks = $xpdl->getElementsByTagName('Task');
			foreach ($tasks as $task)
			{
				$performers = $task->getElementsByTagName('Performers');
				foreach ($performers as $performer)
					$performer->parentNode->removeChild($performer);
			}
			
			return $xpdl;
		}
		
		/**
		 * M�todo para limpar algumas sujeiras que o TIBCO cria
		 * @param DOMDocument $xpdl
		 * @return DOMDocument $xpdl
		 */
		private function clearTibcoNames($xpdl, $nodename)
		{
			$nodes = $xpdl->getElementsByTagName($nodename);
			foreach ($nodes as $node)
				if ($node->getAttribute('xpdExt:DisplayName'))
					$node->setAttribute('Name',$node->getAttribute('xpdExt:DisplayName'));
				
			return $xpdl;
		}
		
		/**
		 * Carrega lista dos Participantes do Contexto
		 * @param DOMDocument $xpdl
		 * @return void
		 */
		private function processXPDLParticipants(DOMDocument $xpdl)
		{
			SanSIS_Wfm_Debug_Debug::log('Associando Participantes do Contexto.');
			
			$participants = $xpdl->getElementsByTagName('Participant');
			if ($participants->length < 1)
				throw new SanSIS_Wfm_Exception_NoParticipantsOnXPDL();
				
			foreach ($participants as $participant)
			{				
				$id = $participant->getAttribute('Id');
				$this->participants[$id] = new SanSIS_Wfm_Engine_ContextParticipant($participant, $this);
			}
			
			SanSIS_Wfm_Debug_Debug::log('Participantes associados ao Contexto.');
		}
		
		/**
		 * Carrega lista dos Processos do Contexto
		 * @param DOMDocument $xpdl
		 * @return void
		 */
		private function processXPDLProcesses(DOMDocument $xpdl)
		{
			SanSIS_Wfm_Debug_Debug::log('Associando Processos do Contexto.');
			
			$wfs = $xpdl->getElementsByTagName('WorkflowProcess');
			if ($wfs->length == 0)
				throw new SanSIS_Wfm_Exception_NoProcessesOnXPDL();
				
			foreach ($wfs as $wf)
			{				
				$id = $wf->getAttribute('Id');
				$this->processes[$id] = new SanSIS_Wfm_Engine_ContextProcess($wf, $this);
			}
			
			SanSIS_Wfm_Debug_Debug::log('Processos associados ao Contexto.');
		}
		
		/**
		 * Carrega lista dos Artefatos do Contexto
		 * @param DOMDocument $xpdl
		 * @return void
		 */
		private function processXPDLDataObjects(DOMDocument $xpdl)
		{
			$dataobjects = $xpdl->getElementsByTagName('Artifact');
			
			SanSIS_Wfm_Debug_Debug::log('Associando Objetos de Dados do Contexto.');
				
			foreach ($dataobjects as $dataobject)
				if ($dataobject->getAttribute('ArtifactType')=="DataObject")
				{
					$id = $dataobject->getAttribute('Id');
					$this->dataobjects[$id] = new SanSIS_Wfm_Engine_ContextDataObject($dataobject, $this);
				}
			
			SanSIS_Wfm_Debug_Debug::log('Objetos de Dados associados ao Contexto.');
		}
		
		/**
		 * Carrega lista das Associa��es do Contexto
		 * @param DOMDocument $xpdl
		 * @return void
		 */
		private function processXPDLAssociations(DOMDocument $xpdl)
		{
			$associations = $xpdl->getElementsByTagName('Association');
			
			SanSIS_Wfm_Debug_Debug::log('Associando Associa��es do Contexto.');
				
			foreach ($associations as $association)
			{
				$id = $association->getAttribute('Id');
				$this->associations[$id] = new SanSIS_Wfm_Engine_ContextAssociation($association, $this);
			}
			
			SanSIS_Wfm_Debug_Debug::log('Associa��es associadas ao Contexto.');
		}
		
		/**
		 * Obt�m um Participante espec�fico do Contexto
		 * @param string $id
		 * @return SanSIS_Wfm_Engine_ContextParticipant
		 */
		public function getParticipant($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Participante "'.$id.'" do Contexto.');
			
			//verifica se existe participante			
			if (isset($this->participants[$id]))
				return $this->participants[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoParticipantByThatId();
		}
		
		/**
		 * Obt�m todos os Participantes do Contexto
		 * @return array SanSIS_Wfm_Engine_ContextParticipant
		 */
		public function getParticipants()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Participantes do Contexto.');
			
			return $this->participants;
		}
		
		/**
		 * Obt�m o Contexto de um Processo espec�fico
		 * @param string $id
		 * @return SanSIS_Wfm_Engine_ContextProcess
		 */
		public function getProcess($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Processo "'.$id.'" do Contexto.');
			
			//verifica se existe participante			
			if (isset($this->processes[$id]))
				return $this->processes[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoProcessByThatId();
		}
		
		/**
		 * Obt�m todos os Processos contidos no Contexto
		 * @return array SanSIS_Wfm_Engine_ContextProcess
		 */
		public function getProcesses()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Processos do Contexto.');
			
			return $this->processes;
		}
		
		/**
		 * Adiciona uma Atividade ao Contexto
		 * @param string $id
		 * @return void
		 */
		public function addActivity(SanSIS_Wfm_Engine_ContextActivity $activity)
		{
			$id = $activity->getId();			
			$name = $activity->getName();
			$this->activities[$id] = $activity;
			
			SanSIS_Wfm_Debug_Debug::log('Atividade "'.$id.' - '.$name.'" associada ao Contexto.');
		}
		
		/**
		 * Obt�m uma Atividade do Contexto
		 * @param string $id
		 * @return SanSIS_Wfm_Engine_ContextActivity
		 */
		public function getActivity($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Atividade "'.$id.'" do Contexto.');
			
			//verifica se existe participante			
			if (isset($this->activities[$id]))
				return $this->activities[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoActivityByThatId();
		}
		
		/**
		 * Obt�m todas as Atividade contidos no Contexto
		 * @return array SanSIS_Wfm_Engine_ContextActivity
		 */
		public function getActivities()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Atividades do Contexto.');
			
			return $this->activities;
		}
		
		/**
		 * Obt�m um Objeto de Dados do Contexto
		 * @param string $id
		 * @return SanSIS_Wfm_Engine_ContextDataObject
		 */
		public function getDataObject($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Objeto de Dados "'.$id.'" do Contexto.');
			
			//verifica se existe participante			
			if (isset($this->dataobjects[$id]))
				return $this->dataobjects[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoDataObjectByThatId();
		}
		
		/**
		 * Obt�m todos os Objetos de Dados contidos no Contexto
		 * @return array SanSIS_Wfm_Engine_ContextDataObject
		 */
		public function getDataObjects()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Objetos de Dados do Contexto.');
			
			return $this->dataobjects;
		}
		
		/**
		 * Obt�m uma Associa��o do Contexto
		 * @param string $id
		 * @return SanSIS_Wfm_Engine_ContextAssociation
		 */
		public function getAssociation($id)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Associa��o "'.$id.'" do Contexto.');
			
			//verifica se existe participante			
			if (isset($this->associations[$id]))
				return $this->associations[$id];
			//se n�o existir lan�a exception
			else
				throw new SanSIS_Wfm_Exception_NoAssociationByThatId();
		}
		
		/**
		 * Obt�m todas as Atividade contidos no Contexto
		 * @return array SanSIS_Wfm_Engine_ContextAssociation
		 */
		public function getAssociations()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Associa��es do Contexto.');
			
			return $this->associations;
		}
		
		/**
		 * Obt�m um Elemento qualquer por Id contidos no Contexto
		 * @param string $id
		 * @return array SanSIS_Wfm_Engine_ContextAssociation
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
					$element = $this->getDataObject($id);
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
							$element = $this->getProcess($id);
						}
						catch (SanSIS_Wfm_Exception_Exception $e)
						{
							try
							{
								$processes = $this->getProcesses();
								foreach ($processes as $process)
								{
									$element = $process->getElementById($id);
								}
							}
							catch (SanSIS_Wfm_Exception_Exception $e)
							{
								throw new SanSIS_Wfm_Exception_NoElementByThatId();
							}						
						}
					}
				}
			}
			
			if (!$element)
				throw new SanSIS_Wfm_Exception_NoElementByThatId();
				
			
			return $element;
		}
		
		/**
		 * Obt�m o Contexto de um Processo espec�fico
		 * @return SanSIS_Wfm_Engine_SanSIS_Wfm_Engine_ContextProcess
		 */
		public function getStructure($process_type)
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo Estrutura do Processo "'.$process_type.'" do Contexto.');
			
			return $this->getProcess($process_type);
		}
		
		/**
		 * Obt�m a defini��o XPDL do Contexto
		 * @return string
		 */
		public function getXPDLDefinition()
		{
			SanSIS_Wfm_Debug_Debug::info('Obtendo XPDL do Contexto.');
			
			return $this->xpdlDefinition;
		}
	}

?>
<?php

/**
 * Formulário para confirmação de remoção de Processo
 *
 * @package		Execution
 * @subpackage	Processo
 * @category	Form
 * @name		Delete
 * @author		Pablo Santiago Sánchez <phackwer@gmail.com> 
 * @version		1.0.0
 */
class Execution_Form_Processo_Delete extends SanSIS_Form {
	
	/**
	 * Inicializa a criação do formulário
	 */
	public function init()
	{
		$id = $this->createElement ( 'Hidden', 'id', array ('value' => '' ) );
		
		$nome = $this->createElement('Text', 'nome', array('label' => 'Nome:', 'required' => true, 'maxLength' => 100, 'style' => 'width:300px'));
		$nome->addValidator('StringLength', false, array(2, 100));
		
		$Confirmar = $this->createElement ( 'submit', 'confirmar', array ('class' => 'button' ) );
		$Cancelar = $this->createElement ( 'submit', 'cancelar', array ('class' => 'button') );
		
		$arElements = $this->addElements ( array ($id, $nome ) );
		
		$this->addDisplayGroup ( array ('nome' ), 'idParticipante', array ('legend' => 'Participante' ) );
		
		$arElements = $this->addElements ( array ($Confirmar, $Cancelar ) );
		
		$this->addDisplayGroup(array('confirmar', 'cancelar'), 'ActionBar');
		
		parent::init ();
	}
}
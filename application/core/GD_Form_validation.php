<?php
/**
 * Copyright (c) 2018. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

class GD_Form_validation extends CI_Form_validation{

	function __construct( $reglas = array() ){
		parent::__construct($reglas);
		$this->ci =& get_instance();
	}


	public function get_reglas(){
		return $this->_config_reglas;
	}
	
	public function get_errores_arreglo(){
		return $this->_error_array;
	}

	public function get_campos( $form_data ){

		$nombres_campos = array();

		$reglas = $this->get_reglas();
		$reglas = $reglas[ $form_data ];


		foreach ($reglas as $i => $info) {
			$nombres_campos[] = $info['field'];
		}

		return $nombres_campos;

	}

}




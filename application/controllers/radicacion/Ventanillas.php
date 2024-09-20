<?php
/**
 * Copyright (c) 2018. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by ProintelPutumayo.
 * User: JairDev
 * Date: 29/08/2018
 * Time: 15:35 AM
 */
defined('BASEPATH') OR exit('No se permite el acceso directo al script');

use \Firebase\JWT\JWT;

class Ventanillas extends GD_Controller
{

    private $nomTablaBd = "";
    private $respuesta;
    private $error_rta;


    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('radicacion/ventanillas_model', 'ventanilla');

        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function combo_get()
    {
        $this->respuesta = $this->ventanilla->getCombo();
        array_push($this->respuesta['data'], ['id' => 'ND', 'nom' => 'No Aplica']);


        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

}
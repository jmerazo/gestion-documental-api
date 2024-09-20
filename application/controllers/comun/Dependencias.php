<?php
defined('BASEPATH') OR exit('No se permite el acceso directo al script');

use \Firebase\JWT\JWT;

class Dependencias extends GD_Controller
{

    private $nomTablaBd = "";
    private $respuesta;
    private $error_rta;


    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        /* $this->auth(); */
        $this->load->database();
        $this->load->model('comun/dependencias_model','dependencias');

        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function combo_get()
    {
        $this->respuesta = $this->dependencias->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }
}
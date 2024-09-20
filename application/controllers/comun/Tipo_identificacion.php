<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 29/08/2018
 * Time: 11:36 AM
 */
use \Firebase\JWT\JWT;
class Tipo_identificacion extends GD_Controller
{
    private $respuesta;
    private $error_rta;


    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('comun/Tipo_identificacion_model','tipo_identificacion');

        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function combo_get()
    {
        $this->respuesta = $this->tipo_identificacion->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }
}
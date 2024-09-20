<?php
/**
 * Created by ProintelPutumayo.
 * User: JairDev
 * Date: 29/08/2018
 * Time: 15:35 AM
 */
defined('BASEPATH') OR exit('No se permite el acceso directo al script');

use \Firebase\JWT\JWT;

class Funcionarios extends GD_Controller
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
        $this->load->model('comun/funcionarios_model', 'funcionarios');

        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function combo_get()
    {
        $this->respuesta = $this->funcionarios->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    public function oficinasOrganigrama_get()
    {
        $this->respuesta = $this->funcionarios->miOrganigrama();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    public function allActivos_get()
    {

        $this->respuesta = $this->funcionarios->getFuncActivos();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Externos extends GD_Controller
{
    private $result;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function buscaTerceroXDocumento_get()
    {
        $tipoDocumento = $this->uri->segment(4);
        $nroDocumento = $this->uri->segment(5);

        $this->load->database();
        $this->load->model('Terceros_model', 'bdTerceros');

        $rta = $this->bdTerceros->getSerarchXDocumento($tipoDocumento, $nroDocumento);

        $this->respuesta($rta, $this->error_rta);
    }
}
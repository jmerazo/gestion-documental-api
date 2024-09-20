<?php

class Reporte_cosolidado extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    function consolidado_get($token, $filtro)
    {
        $filtro = base64_decode($filtro);
        $token = base64_decode($token);
        $filtro = json_decode($filtro, true);

        //var_dump($filtro);
        //exit();

        if ($filtro['reporte'] == 'consolidadoRadicados') {
            $this->load->helper('RepConsolidadoOficina_xlsx_helper');
            repConsolidadoBySecretarias($filtro);
        }


    }

}


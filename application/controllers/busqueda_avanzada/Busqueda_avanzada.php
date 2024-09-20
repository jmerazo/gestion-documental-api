<?php

/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 27/08/2018
 * Time: 5:16 PM
 */

class Busqueda_avanzada extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('Busqueda_avanzada_model', 'Busqueda_avanzada');
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function busqueda_post()
    {
        $cadena = $this->uri->segment(4);
        $tipoConsulta = $this->uri->segment(5);

        $rta  = $this->Busqueda_avanzada->busqueda($cadena, $tipoConsulta);

        //print_r("lo que sale en rta busqueda: ");
        //($rta["data"][0]);

        //print_r(" tamanio arreglo rta : ");
        //print_r( count($rta["data"] ) );

        for ($i = 0; $i < count($rta["data"]); $i++) {

            $rta["data"][$i]["Remite"] = json_decode( $rta["data"][$i]["Remitente"] );

            unset($rta["data"][$i]["Remitente"]);

        }

        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }

        $this->respuesta($rta, $this->error_rta);
    }

    public function paginar_post()
    {
        $this->load->helper('paginador_helper');
        $cmps = ['cod_radicado','radicado_numero_documento_radicado'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        //$condicion="estado = 1";
        $condicion=1;
        $data = paginar_todos('ges_metadatos_full', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        unset($data['sql']);
        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }
}
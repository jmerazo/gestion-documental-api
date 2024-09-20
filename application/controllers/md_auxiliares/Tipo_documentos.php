<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 11/08/2018
 * Time: 2:39 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Tipo_documentos extends GD_Controller
{

    private $respuesta;
    private $error_rta;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $this->load->database();

        $this->load->model('Tipo_documentos_model', 'mdTipDoc');
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function save_put()
    {
        $this->auth();
        $data = $this->put();
        $this->load->library('form_validation');

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('tipo_documento_put')) {

            $tipo = $this->mdTipDoc->set_data($data);
            $this->respuesta = $tipo->insert();

            if ($this->respuesta['error']) {
                $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            }

        } else {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => true,
                'msg' => 'Hay errores en la información del formulario',
                'errores' => $this->form_validation->error_array(),
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function edit_post()
    {
        $this->auth();
        $codigo = $this->uri->segment(4);

        $data = $this->post();
        $data['ide_tipo_documental'] =$codigo;

        $this->load->library('form_validation');
        $this->form_validation->set_data($data);

        if ($this->form_validation->run('tipo_documento_post')) {

            $tipo_documental = $this->mdTipDoc->set_data($data);
            $this->respuesta = $tipo_documental->update();

            if ($this->respuesta['error']) {
                $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            }

        } else {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => true,
                'msg' => 'Hay errores en la información del formulario',
                'errores' => $this->form_validation->error_array(),
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function elimina_delete()
    {
        $this->auth();
        try {
            $codigo = $this->uri->segment(4);
            if (empty($codigo)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Se requiere mas informacion para eliminar el registro', $this->error_rta);
            }
            $cnd = 'cod_entidad =' . $this->db->escape($codigo);
            $rta = $this->mdTipDoc->eliminar($codigo);
            $this->respuesta = [
                'msg' => 'Resgistro eliminados  ',
                'error' => false
            ];

        } catch (Exception $exc) {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => true,
                'error_num' => $exc->getCode(),
                'msg' => $exc->getMessage(),
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function paginar_post()
    {
        $this->auth();
        $this->load->helper('paginador_helper');
        $cmps = ['ide_tipo_documental as pk', 'tipo_documental', 'descripcion_tipo_documental','dias_tramite'
            ,'tipo_documento'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion="estado = 1";
        $data = paginar_todos('ges_tipos_documentales', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        unset($data['sql']);
        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function combo_get()
    {
        $this->auth();
        $this->respuesta = $this->mdTipDoc->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }
    public function comboPqrd_get()
    {
        $this->respuesta = $this->mdTipDoc->getComboPqrd();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }
}
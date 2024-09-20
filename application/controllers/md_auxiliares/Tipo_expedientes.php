<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 11/08/2018
 * Time: 2:39 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Tipo_expedientes extends GD_Controller
{

    private $respuesta;
    private $error_rta;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
         $this->auth();
        $this->load->database();
        $this->load->model('Tipo_expedientes_model', 'bdTipExp');
        $this->error_rta = GD_Controller::HTTP_OK;
    }
    public function combo_get()
    {
        $this->respuesta = $this->bdTipExp->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    public function save_put()
    {
        $data = $this->put();
        $this->load->library('form_validation');

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('tipo_exp_put')) {

            $tipo_exp = $this->bdTipExp->set_data($data);
            $this->respuesta = $tipo_exp->insert();

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
        $codigo = $this->uri->segment(4);

        $data = $this->post();
        $data['id_tipo_documento'] =$codigo;

        $this->load->library('form_validation');
        $this->form_validation->set_data($data);

        if ($this->form_validation->run('tipo_exp_post')) {

            $tipo_exp = $this->bdTipExp->set_data($data);
            $this->respuesta = $tipo_exp->update();

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
        try {
            $codigo = $this->uri->segment(4);
            if (empty($codigo)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Se requiere mas información para eliminar el registro', $this->error_rta);
            }
            $cnd = 'cod_entidad =' . $this->db->escape($codigo);

            $rta = $this->bdTipExp->eliminar($codigo);
            $this->respuesta = [
                'msg' => 'Resgistro eliminado',
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
        $this->load->helper('paginador_helper');
        $cmps = ['id_tipo_documento as pk', 'nombre_tipo', 'descripcion_tipo'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion="estado = 1";
        $data = paginar_todos('ges_tipos_expedientes', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        unset($data['sql']);
        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }
}
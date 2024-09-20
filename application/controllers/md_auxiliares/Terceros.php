<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 11/08/2018
 * Time: 2:39 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Terceros extends GD_Controller
{
    private $respuesta;
    private $error_rta;
    private $campoSearch;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('Terceros_model', 'bdTerceros');
        $this->error_rta = GD_Controller::HTTP_OK;

        $this->campoSearch = [['id' => 'nombres_tercero'], ['id' => 'apellidos_tercero'], ['id' => 'nom_entidad'],
            ['id' => 'nit_tercero'], ['id' => 'mail_tercero']
        ];
}

    public function save_put()
    {
        $data = $this->put();
        $this->load->library('form_validation');

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('terceros_put')) {
            $nombres_tercero = $this->bdTerceros->set_data($data);
            $this->respuesta = $nombres_tercero->insert();

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
        $data['ide_tercero'] = $codigo;

        $this->load->library('form_validation');
        $this->form_validation->set_data($data);

        if ($this->form_validation->run('terceros_post')) {

            $nombres_tercero = $this->bdTerceros->set_data($data);
            $this->respuesta = $nombres_tercero->update();

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
                throw new Exception('Se requiere mas inofrmacion para eliminar el registro', $this->error_rta);
            }
            $cnd = 'cod_entidad =' . $this->db->escape($codigo);

            $rta = $this->bdTerceros->eliminar($codigo);
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
        $cmps = ['ide_tercero as pk','ide_tipo_identf', 'nit_tercero as nit_tercero', 'tipo_tercero', 'nombres_tercero',
            'apellidos_tercero', 'tel_fijo_tercero', 'cel_fijo_tercero', 'ide_divipola_tercero',
            'direccion_tercero', 'mail_tercero','nom_entidad'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion = "estado = 1";

        $data = paginar_todos('rad_terceros', $cmps, $this->campoSearch, $parambus, null,
            $nroPagina, 15, $campoOrden, $orden);
        unset($data['sql']);
        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function comboSearch_post()
    {
        $search=$this->input->post('_search',true);
        $this->respuesta = $this->bdTerceros->searchCombo($search);
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

}
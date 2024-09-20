<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 11/09/2018
 * Time: 10:05 AM
 */

class Roles  extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('Roles_model', 'roles');
        $this->error_rta = GD_Controller::HTTP_OK;
    }
    public function save_put()
    {

        $data = $this->put();
        $this->load->library('form_validation');

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('roles_put')) {

            $rol = $this->roles->set_data($data);
            $this->respuesta = $rol->insert();

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
        $data['ide_rol'] =$codigo;

        $this->load->library('form_validation');
        $this->form_validation->set_data($data);

        if ($this->form_validation->run('roles_post')) {

            $rol = $this->roles->set_data($data);
            $this->respuesta = $rol->update();

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
            $cnd = 'ide_rol =' . $this->db->escape($codigo);

            $rta = $this->roles->eliminar($codigo);
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
    public function combo_get()
    {
        $this->respuesta = $this->roles->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }
    public function paginar_post()
    {
        $this->load->helper('paginador_helper');
        $cmps = ['ide_rol as pk','nombre_rol','descripcion_rol'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion="estado = 1";
        $data = paginar_todos('seg_roles', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        unset($data['sql']);
        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta);
    }
}
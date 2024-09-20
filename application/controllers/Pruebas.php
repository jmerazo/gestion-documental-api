<?php
/**
 * Created by Prointel Putumayo.
 * User: Jair Muñoz
 * Date: 23/07/18
 * Time: 22:18
 */

defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

#require APPPATH . 'libraries/REST_Controller.php';

class Pruebas extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        // $this->auth();
        $this->load->database();
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function prueba_put()
    {

        $this->respuesta = $this->put();

        #$this->respuesta = ['msg' => 'Nuevo resgistro creado'];
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function prueba_post()
    {
        $this->respuesta = ['msg' => 'Resgistro modificado'];

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function prueba_delete()
    {
        try {
            $codigo = $this->uri->segment(3);


            if (empty($codigo)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Se requiere mas inofrmacion para eliminar el registro', $this->error_rta);
            }
            $cnd = 'cod_entidad =' . $this->db->escape($codigo);
            //$this->msg = $this->comun->remove($this->nomTableBd, $cnd, $this->Permisos);
            $this->respuesta = [
                'msg' => 'Resgistro eliminado',
                'error' => false
            ];

        } catch (Exception $exc) {
            $this->respuesta = [
                'error' => true,
                'error_num' => $exc->getCode(),
                'msg' => $exc->getMessage(),
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public
    function paginar_post()
    {
        $this->load->helper('paginador_helper');
        $cmps = ['*'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');

        $data = paginar_todos('rad_terceros', $cmps, null, null, null,
            $nroPagina, 10, $campoOrden);
        unset($data['sql']);
        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

}

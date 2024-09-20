<?php
class Usuarios extends GD_Controller
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
        $this->load->model('Usuarios_model', 'BbUsuarios');
        $this->error_rta = GD_Controller::HTTP_OK;
        $this->campoSearch = [['id' => 'documento'], ['id' => 'nombres'], ['id' => 'apellidos'],
            ['id' => 'login'], ['id' => 'mail_user']
        ];
    }

    public function save_put()
    {
        $data = $this->put();
        $this->load->library('form_validation');

        $data['config_users'] = json_encode($data['cnf'], JSON_NUMERIC_CHECK);
        unset($data['cnf']);

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('usuarios_put')) {
            $data['pass'] = encryptar($data['pass']);
            $user = $this->BbUsuarios->set_data($data);
            $this->respuesta = $user->insert();

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
        $data['cod_user'] = $codigo;

        $data['config_users'] = json_encode($data['cnf'], JSON_NUMERIC_CHECK);
        unset($data['cnf']);

        $this->load->library('form_validation');
        $this->form_validation->set_data($data);

        if ($this->form_validation->run('usuarios_post')) {

            $user = $this->BbUsuarios->set_data($data);
            $this->respuesta = $user->update();
            //echo $this->db->last_query(); al estar esto activo no dejaba editar usuarios en admin usuarios
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

    public function changepass_post()
    {
        $codigo = $this->uri->segment(4);

        $data = $this->post();
        $data['cod_user'] = $codigo;

        $this->load->library('form_validation');
        $this->form_validation->set_data($data);

        if ($this->form_validation->run('changePassUser_post')) {

            $user = $this->BbUsuarios->set_data($data);
            $this->respuesta = $user->ChangePass();

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

            $rta = $this->BbUsuarios->eliminar($codigo);
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
        $cmps = ['cod_user as pk', 'documento', 'nombres', 'apellidos', 'login', 'mail_user', 'nombre_rol'
            , 'dependencia_id','cargo_id'
            , 'ide_rol', 'config_users as cnf'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion = "estado_user = 1";
        $data = paginar_todos('vwv_usuarios', $cmps, $this->campoSearch, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        unset($data['sql']);
        $cont = 0;
        foreach ($data['rows'] as $r) {
            $dtsx[] = $r;
            $dtsx[$cont++]['cnf'] = json_decode($r['cnf']);
        }
        $data['rows'] = $dtsx;
        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }
}
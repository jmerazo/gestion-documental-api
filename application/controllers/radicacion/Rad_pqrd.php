<?php

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';

use \Firebase\JWT\JWT;

class Rad_pqrd extends REST_Controller
{
    private $result;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('radicacion/radicar_model', 'bdRadicar');
        $this->error_rta = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->result = [
            'error' => true,
            'msg' => 'Falta información',
            'errores' => [],
        ];
    }

    public function save_put()
    {
        $data = $this->put();
        $this->load->library('form_validation');
        $data['ide_tipo_radicado'] = 1;
        $data['activo_radicado'] = 'S';
        $data['nom_usuario'] = 'web.pqrd';

        $data['fecha_registro'] = date('Y-m-d H:m:s');
        $data['fecha_radicado'] = date('Y-m-d H:m:s');
        $data['fecha_documento_radicado'] = date('Y-m-d H:m:s');
        $data['ide_anexo_a'] = null;
        $data['ide_ventanilla'] = ID_VENTANILLA_WEB;
        $data['ide_medio_recepcion'] = ID_MEDIO_RECEPCION;

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('rad_entrada_put')) {

            $radicado = $this->bdRadicar->set_data($data);
            $this->result = $radicado->insertEntrada();

            $this->error_rta=REST_Controller::HTTP_OK;

        } else {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->result = [
                'error' => true,
                'msg' => 'Hay errores en la información del formulario',
                'errores' => $this->form_validation->error_array(),
            ];
        }
        $this->response($this->result,  $this->error_rta);
    }

}
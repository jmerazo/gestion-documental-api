<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';

use \Firebase\JWT\JWT;

class GD_Controller extends REST_Controller
{
    public $certAut;
    public $cnfUser = null;

    function __construct()
    {
        parent::__construct();
    }

    public function genToken($user_data = [])
    {
        $aud = AudGetRealIP();
        $tk = [
            'data' => $user_data,
            'aud' => $aud,
            'iat' => time(),
            'exp' => time() + (1 * 8 * 60 * 60)     // 1 días; 8 horas; 60 minutos; 60 segundos
        ];
        return JWT::encode($tk, $this->config->item('encryption_key'));
    }

    public function auth($token = '')
    {
        $headers = $this->input->get_request_header('Jwt');
        log_message('debug', 'Encabezado Jwt recibido: ' . $headers);

        $kunci = $this->config->item('encryption_key');
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $token = $matches[1];
                log_message('debug', 'Token extraído: ' . $token);
            }
        }

        try {
            $decoded = JWT::decode($token, $kunci, array('HS256'));
            $this->certAut = $decoded->data;
            $this->cnfUser = $this->certAut->config;
            log_message('debug', 'Token decodificado correctamente');
        } catch (Exception $e) {
            $invalid = [
                'error' => true,
                'msg' => $e->getMessage(),
                'errores' => 'Se cierra la sesión por inactividad',
            ];
            $this->response($invalid, 401);
        }
    }

    public function sendResponse($data, $message = '', $status = GD_Controller::HTTP_OK)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];

        // Establecer el encabezado Content-Type a application/json
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status)
            ->set_output(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK))
            ->_display();
        exit;
    }

    public function viewFile($archivo)
    {
        $this->output
            ->set_content_type('application/pdf')
            ->set_output(file_get_contents($archivo));
    }

    public function respuesta($data = NULL, $http_code = NULL)
    {
        $this->output->set_header('Jwt: ' . $this->genToken($this->certAut));
        $this->response($data, $http_code);
    }
}
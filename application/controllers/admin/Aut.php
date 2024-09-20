<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Aut extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->error_rta = GD_Controller::HTTP_OK;
        $this->load->model('Usuarios_model');
    }

    public function aut_post()
    {
        try {
            $this->load->model('auth_model', 'model_aut');
            $email = $this->input->post('usuario'); // Ahora 'usuario' es el correo electrónico
            $pass = $this->input->post('pwd');

            if (empty($pass) OR empty($email)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Correo electrónico y contraseña son requeridos', 404);
            }

            // Pasamos la contraseña en texto plano al modelo
            $user_data = $this->model_aut->auth($email, $pass);

            if (is_null($user_data)) {
                $this->error_rta = GD_Controller::HTTP_NOT_FOUND;
                throw new Exception('Credenciales inválidas o cuenta no verificada', 404); // Ajusta el mensaje
            }

            $aud = AudGetRealIP();
            $user_data['config'] = json_decode($user_data['config']);
            $tk = [
                'data' => $user_data,
                'aud' => $aud,
                'iat' => time(),
                'exp' => time() + (1 * 8 * 60 * 60)     // 1 día; 8 horas; 60 minutos; 60 segundos
            ];

            $jwt = JWT::encode($tk, $this->config->item('encryption_key'));
            $this->respuesta = ['msg' => '', 'err' => false, 'tk' => $jwt];	
        } catch (Exception $exc) {
            $this->respuesta = [
                'msg' => $exc->getMessage(),
                'err' => true,
                'data' => null
            ];
        }
        return $this->response($this->respuesta, $this->error_rta);
    }

    public function menu_get()
    {
        $this->auth();
        $this->load->model('auth_model', 'auth');
        $menu = $this->auth->menu($this->certAut->idu, $this->certAut->ide_rol);
        $this->respuesta(array_values($menu), $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function register_post()
    {
        try {
            $data = $this->post();
            $this->load->library('form_validation');

            // Configuración de reglas de validación
            $this->form_validation->set_data($data);
            $this->form_validation->set_rules('documento', 'Nro. Documento', 'required');
            $this->form_validation->set_rules('nombres', 'Nombres', 'required');
            $this->form_validation->set_rules('apellidos', 'Apellidos', 'required');
            $this->form_validation->set_rules('mail_user', 'Correo electrónico', 'required|valid_email|is_unique[seg_usuarios.mail_user]');
            $this->form_validation->set_rules('pass', 'Contraseña', 'required|min_length[6]');
            $this->form_validation->set_rules('pass_confirm', 'Confirmar Contraseña', 'required|matches[pass]');
            $this->form_validation->set_rules('dependencia_id', 'Dependencia', 'required');

            if ($this->form_validation->run()) {
                // Encriptar contraseña
                $data['pass'] = password_hash($data['pass'], PASSWORD_BCRYPT);
                unset($data['pass_confirm']);

                // Crear el token de verificación antes de insertar el usuario
                $tokenData = [
                    'mail_user' => $data['mail_user'],
                    'iat' => time(),
                    'exp' => time() + (60 * 60 * 24) // 24 horas
                ];
                $token = JWT::encode($tokenData, $this->config->item('encryption_key'));
                $data['remember_token'] = $token;
                $cleanToken = trim(preg_replace('/\s+/', '', $token));

                // Construir el array con la estructura que necesitas
                $configData = ['filtro' => $data['dependencia_id']];
                $data['config_users'] = json_encode($configData);
                $data['estado_user'] = 0; // Usuario inactivo hasta verificación
                $data['ide_rol'] = 3; // ID del rol predeterminado
                $data['verified_email'] = 0;

                // Guardar usuario en la base de datos
                $this->load->model('Usuarios_model');
                $response = $this->Usuarios_model->register($data);               

                if ($response['error']) {
                    $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    throw new Exception('Error al registrar el usuario');
                }

                // Enviar correo de verificación
                $this->load->library('email');
                $this->email->from('no-reply@putumayo.gov.co', 'GesDoc | Gobernación del Putumayo');
                $this->email->to($data['mail_user']);
                $this->email->subject('GesDoc | Verificación de cuenta');

                // Ruta del logo
                $logoPath = FCPATH . 'images/gesdoc_v2.png'; // Usa la ruta completa en el sistema de archivos
                $this->email->attach($logoPath, 'inline');
                $cid = $this->email->attachment_cid($logoPath);

                // Crear enlace de verificación
                //$frontendURL = 'http://gesdoc.putumayo.gov.co';
                $frontendURL = 'http://127.0.0.1:5500'; // Cambia esto a la URL de tu frontend real
                $verificationLink = $frontendURL . '/#!/page/verify-account?token=' . rawurlencode($cleanToken);

                // Diseño del mensaje HTML con logo y botón estilo GOV.CO
                $message = '
                    <div style="font-family: Arial, sans-serif; color: #333;">
                        <div style="text-align: center; padding: 20px;">
                            <img src="cid:' . $cid . '" alt="GesDoc Logo" style="width: 200px; height: auto;" />
                        </div>
                        <div style="padding: 20px; background-color: #f4f4f4; border-radius: 10px; text-align: center;">
                            <p style="font-size: 18px;">Estimado ' . $data['nombres'] . ',</p>
                            <p style="font-size: 16px;">Gracias por registrarte. Por favor, haz clic en el siguiente botón para verificar tu cuenta:</p>
                            <a href="' . $verificationLink . '" style="
                                display: inline-block;
                                padding: 12px 25px;
                                font-size: 16px;
                                color: white;
                                background-color: #005BAC;
                                border-radius: 5px;
                                text-decoration: none;
                                font-weight: bold;
                                white-space: nowrap;
                            ">Verificar Cuenta</a>                
                            <p style="margin-top: 30px;">Saludos,<br>Equipo de GesDoc</p>
                        </div>
                    </div>
                ';

                $this->email->message($message);

                if (!$this->email->send()) {
                    $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    throw new Exception('Error al enviar el correo de verificación');
                }

                $this->respuesta = [
                    'error' => false,
                    'msg' => 'Registro exitoso. Por favor, revisa tu correo electrónico para verificar tu cuenta.'
                ];
            } else {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                $this->respuesta = [
                    'error' => true,
                    'msg' => 'Hay errores en la información del formulario',
                    'errores' => $this->form_validation->error_array(),
                ];
            }
        } catch (Exception $e) {
            $this->respuesta = [
                'error' => true,
                'msg' => $e->getMessage(),
            ];
        }

        $this->response($this->respuesta, $this->error_rta);
    }

    public function verify_get()
    {
        $token = $this->input->get('token');

        try {
            if (empty($token)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Token es requerido');
            }

            // Decodificar el token
            $tokenData = JWT::decode($token, $this->config->item('encryption_key'), ['HS256']);

            // Obtener usuario por token
            $this->load->model('Usuarios_model');
            $user = $this->Usuarios_model->get_user_by_token($token);

            if (empty($user)) {
                $this->error_rta = GD_Controller::HTTP_NOT_FOUND;
                throw new Exception('Token inválido o usuario no encontrado');
            }

            // Si el correo ya fue verificado previamente
            if ($user['verified_email'] == 1) {
                $this->respuesta = [
                    'error' => false,
                    'msg' => 'La cuenta ya ha sido verificada previamente.'
                ];
            } else {
                // Actualizar el campo 'verified_email' a 1
                $updateData = [
                    'verified_email' => 1 // Cambiar el campo 'verified_email' a 1
                ];

                // Llamar al método update del modelo Usuarios_model
                $this->respuesta = $this->Usuarios_model->update($user['cod_user'], $updateData);

                if ($this->respuesta['error']) {
                    $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    throw new Exception('Error al activar la cuenta');
                }

                // Eliminar el token después de la verificación
                $this->Usuarios_model->remove_verification_token($user['cod_user']);

                $this->respuesta = [
                    'error' => false,
                    'msg' => 'Cuenta verificada exitosamente.'
                ];
            }
        } catch (Exception $e) {
            $this->respuesta = [
                'error' => true,
                'msg' => $e->getMessage(),
            ];
        }

        // Puedes retornar la respuesta como JSON si la verificación es vía API
        $this->response($this->respuesta, $this->error_rta);
    }

    // Listar usuarios pendientes de validación
    public function listar_pendientes_get() {
        $usuarios = $this->Usuarios_model->get_usuarios_pendientes_validacion();$this->load->model('Usuarios_model');

        if (!empty($usuarios)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => false, 'usuarios' => $usuarios]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => true, 'msg' => 'No hay usuarios pendientes de validación']));
        }
    }

    // Validar un usuario
    public function activar_usuario_post($cod_user) {
        $resultado = $this->Usuarios_model->activar_usuario($cod_user);

        if ($resultado) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => false, 'msg' => 'Usuario validado correctamente']));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => true, 'msg' => 'Error al validar el usuario']));
        }
    }

    public function rechazar_usuario_post($cod_user) {
        $resultado = $this->Usuarios_model->rechazar_usuario($cod_user);

        if ($resultado) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => false, 'msg' => 'Usuario validado correctamente']));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => true, 'msg' => 'Error al validar el usuario']));
        }
    }
}

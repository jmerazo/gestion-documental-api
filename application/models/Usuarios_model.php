<?php
class Usuarios_model extends CI_Model
{
    public $cod_user;
    public $nombres;
    public $apellidos;
    public $login;
    public $pass;
    public $mail_user;
    public $estado_user = 1;
    public $ide_rol;
    public $editable = "S";
    public $ide_funcionario = "1";
    public $config_users;
    public $documento = null;
    public $cargo_id = null;
    public $dependencia_id = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Usuarios_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        return $this->db->error();
    }

    public function insert()
    {
        $rta = $this->db->insert('seg_usuarios', $this);
		//echo $rta;
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_user' => $this->db->insert_id()
            ];
        } else {
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $this->error(),
                'error_num' => $this->error()
            ];
        }
        return $respuesta;
    }

    public function update($cod_user, $data)
    {
        $this->db->where('cod_user', $cod_user);
        $rta = $this->db->update('seg_usuarios', $data);
        #echo $this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro actualizado correctamente",
                'cd_user' => $cod_user
            ];
        } else {
            $respuesta = [
                "error" => true,
                'msg' => "Error al actualizar el registro",
                'error_msg' => $this->error(),
                'error_num' => $this->error()
            ];
        }
        return $respuesta;
    }

    public function ChangePass()
    {
        $dts = ['pass' => encryptar($this->pass)];
        $this->db->where('cod_user', $this->cod_user);
        $rta = $this->db->update('seg_usuarios', $dts);
        #echo $this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Credenciales cambiadas correctamente",
                'cd_user' => $this->cod_user
            ];
        } else {
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $this->error(),
                'error_num' => $this->error()
            ];
        }
        return $respuesta;
    }

    public function eliminar($codigo)
    {
        $this->db->set('estado_user', false);
        $this->db->where('cod_user', $codigo);
        $rta = $this->db->update('seg_usuarios');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }

    // Método para registrar el usuario
    public function register($data)
    {
        try {
            // Insertar los datos en la tabla 'seg_usuarios'
            $this->db->insert('seg_usuarios', $data);

            // Verificar si hubo algún error en la inserción
            $error = $this->db->error();
            if ((int)$error['code'] > 0) {
                // Si hay un error, devuelve el mensaje del error
                return ['error' => true, 'msg' => 'Error en la inserción: ' . $error['message']];
            }

            // Verificar si la inserción fue exitosa
            if ($this->db->affected_rows() == 1) {
                // Devolver éxito si se inserta un registro
                return ['error' => false, 'msg' => 'Usuario registrado correctamente'];
            } else {
                // Si no se inserta ningún registro, devolver un error genérico
                return ['error' => true, 'msg' => 'Error desconocido al registrar el usuario'];
            }

        } catch (Exception $e) {
            // En caso de excepción, devolver el mensaje de error
            return ['error' => true, 'msg' => 'Excepción al registrar usuario: ' . $e->getMessage()];
        }
    }

    // Método para obtener un usuario por correo electrónico
    public function get_by_email($email)
    {
        $query = $this->db->get_where('seg_usuarios', ['mail_user' => $email]);

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null; // No se encontró el usuario
        }
    }

    // Método para activar al usuario (opcionalmente por email o token)
    public function activate_user($email)
    {
        $this->db->where('mail_user', $email);
        $this->db->update('seg_usuarios', ['estado_user' => 1]); // Cambiar el estado del usuario a activo

        if ($this->db->affected_rows() == 1) {
            return true; // Activación exitosa
        } else {
            return false; // Falló la activación
        }
    }

    // Método opcional: Almacenar token de verificación en la base de datos
    public function store_verification_token($user_id, $token)
    {
        // Actualizar el campo remember_token con el token
        $this->db->where('cod_user', $user_id);
        $this->db->update('seg_usuarios', ['remember_token' => $token]);

        // Verificar si hubo algún error
        if ($this->db->affected_rows() == 1) {
            return true; // Token almacenado correctamente
        } else {
            return false; // Falló el almacenamiento del token
        }
    }

    // Método opcional: Verificar token en la base de datos
    public function verify_token($token)
    {
        $query = $this->db->get_where('user_tokens', ['verification_token' => $token, 'token_expiry >' => time()]);
        if ($query->num_rows() > 0) {
            return $query->row_array(); // Token válido
        } else {
            return null; // Token no válido o expirado
        }
    }

    public function get_user_by_token($token)
    {
        $query = $this->db->get_where('seg_usuarios', ['remember_token' => $token]);

        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return null; // No se encontró el token
        }
    }

    public function remove_verification_token($user_id)
    {
        $this->db->where('cod_user', $user_id);
        $this->db->update('seg_usuarios', ['remember_token' => null]);

        if ($this->db->affected_rows() == 1) {
            return true; // Token eliminado correctamente
        } else {
            return false; // Falló la eliminación del token
        }
    }

    // Obtener lista de usuarios pendientes de validación
    public function get_usuarios_pendientes_validacion() {
        $this->db->select('cod_user, nombres, apellidos, mail_user, login, validate_user, estado_user');
        $this->db->from('seg_usuarios');
        
        // Filtrar usuarios que no han sido validados y que ya verificaron su correo
        $this->db->where('(validate_user IS NULL OR validate_user = "")');
        $this->db->where('verified_email', 1); // Solo usuarios con email verificado
        
        $query = $this->db->get();
    
        if ($query->num_rows() > 0) {
            return $query->result_array(); // Devuelve la lista de usuarios pendientes
        } else {
            return [];
        }
    }    

    // Método para actualizar la validación del usuario y activar el estado
    public function activar_usuario($cod_user) {
        $this->db->where('cod_user', $cod_user);
        $this->db->update('seg_usuarios', [
            'validate_user' => 1, // Actualiza la validación del usuario
            'estado_user' => 1 // Activa el usuario
        ]);

        return $this->db->affected_rows() > 0;
    }

    public function rechazar_usuario($cod_user) {
        $this->db->where('cod_user', $cod_user);
        $this->db->delete('seg_usuarios'); // Elimina el usuario de la base de datos
    
        return $this->db->affected_rows() > 0;
    }
}
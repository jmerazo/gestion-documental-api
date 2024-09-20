<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 13/08/2018
 * Time: 5:38 PM
 */

class Anexos_model extends CI_Model
{

    /*#campos de la tabla en la Bd*/


    public $ide_anexo;
    public $nom_usuario_carga;
    public $fecha_sys;
    public $observaciones;
    public $ide_unidad_dctal;
    public $id_tipo_anexo;
    public $usuario;
    public $nro_documento;
    public $fecha_documento;
    public $ruta_file;
    public $estado_anexo = 1;
    public $raw_json_file = null;

    private $error_msg = null;
    private $error_nro = 0;


    public function __construct()
    {
        parent::__construct();
        $this->fecha_sys = date('Y-m-d H:i:s');
    }


    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Anexos_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        $error = $this->db->error();
        $this->error_msg = $error['message'];
        $this->error_nro = $error['code'];
    }

    public function insert()
    {
        $rta = $this->db->insert('ges_anexos_radicados', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'id_anexo' => $this->db->insert_id()
            ];
        } else {
            $this->error();
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $this->error_msg,
                'error_num' => $this->error_nro
            ];
        }
        return $respuesta;
    }

    public function update()
    {
        $this->db->where('ide_tipo_anexo', $this->ide_tipo_anexo);
        $rta = $this->db->update('ges_anexos_radicados', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_tanexo' => $this->ide_tipo_anexo
            ];
        } else {
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $this->error_msg,
                'error_num' => $this->error_nro
            ];
        }
        return $respuesta;
    }

    public function eliminar($codigo)
    {
        $this->db->set('estado_anexo', false);
        $this->db->where('ide_anexo', $codigo);
        $rta = $this->db->update('ges_anexos_radicados');

        if ($rta) {
            return true;
        } else {
            $this->db->error();
            throw new Exception($this->error_nro, $this->error_msg);
        }
    }

    public function getAnexo($codigo)
    {
        $this->db->where('ide_anexo', $codigo);
        $this->db->select('raw_json_file,ruta_file');
        $rta = $this->db->get('ges_anexos_radicados')->first_row();

        if ($rta) {
            return $rta;
        } else {
            return false;
        }
    }

}
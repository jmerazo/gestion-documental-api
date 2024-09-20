<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 13/08/2018
 * Time: 5:38 PM
 */

class Tipo_expedientes_model extends CI_Model
{
    public $id_tipo_documento;
    public $nombre_tipo;
    public $descripcion_tipo;
    public $estado = 1;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCombo()
    {
        $query = $this->db->select('id_tipo_documento as id,nombre_tipo as nom')
            ->where('estado', 1)
            ->get('ges_tipos_expedientes');

        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;

    }
    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Tipo_expedientes_model", $campo)) {
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
        $rta = $this->db->insert('ges_tipos_expedientes', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_tp_expediente' => $this->db->insert_id()
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

    public function update()
    {
        $this->db->where('id_tipo_documento', $this->id_tipo_documento);
        $rta = $this->db->update('ges_tipos_expedientes', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_tp_expediente' => $this->id_tipo_documento
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
        $this->db->set('estado', false);
        $this->db->where('id_tipo_documento', $codigo);
        $rta = $this->db->update('ges_tipos_expedientes');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }
}
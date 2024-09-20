<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 28/08/2018
 * Time: 10:28 AM
 */

class Natural_documento_model extends CI_Model
{
    public $ide_medio_recepcion;
    public $medio_recepcion;
    public $descripcion_medio;
    public $estado = 1;

    public function __construct()
    {
        parent::__construct();
    }
    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Natural_documento_model", $campo)) {
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
        $rta = $this->db->insert('rad_medios_recepciones', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_natural' => $this->db->insert_id()
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
        $this->db->where('ide_medio_recepcion', $this->ide_medio_recepcion);
        $rta = $this->db->update('rad_medios_recepciones', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_natural' => $this->ide_medio_recepcion
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
        $this->db->where('ide_medio_recepcion', $codigo);
        $rta = $this->db->update('rad_medios_recepciones');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }

    public function getCombo()
    {
        $query = $this->db->select('ide_medio_recepcion as id,medio_recepcion as nom')
            ->where("estado",1)
            ->get('rad_medios_recepciones');

        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }
}
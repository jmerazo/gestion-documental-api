<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 13/08/2018
 * Time: 5:38 PM
 */

class Tipo_anexos_model extends CI_Model
{

    /*#campos de la tabla en la Bd*/

    public $ide_tipo_anexo;
    public $tipo_anexo;
    public $observaciones_tipo_anexo;
    public $estado = 1;

    private $error_msg = null;
    private $error_nro = 0;


    public function __construct()
    {
        parent::__construct();
    }



    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Tipo_anexos_model", $campo)) {
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
        $rta = $this->db->insert('ges_tipos_anexos', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_tp_anexo' => $this->db->insert_id()
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

    public function update()
    {
        $this->db->where('ide_tipo_anexo', $this->ide_tipo_anexo);
        $rta = $this->db->update('ges_tipos_anexos', $this);
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
        $this->db->set('estado', false);
        $this->db->where('ide_tipo_anexo', $codigo);
        $rta = $this->db->update('ges_tipos_anexos');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }


    public function getCombo()
    {
        $query = $this->db->select('ide_tipo_anexo as ide,tipo_anexo  as nom')
            ->where('estado', 1)
            ->get('ges_tipos_anexos');

        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 13/08/2018
 * Time: 5:38 PM
 */

class Tipo_documentos_model extends CI_Model
{

    public $ide_tipo_documental;
    public $ide_serie = 0;
    public $tipo_documental;
    public $descripcion_tipo_documental;
    public $dias_habiles = "H";
    public $respuesta = "S";
    public $dias_tramite = 0;
    public $tipo_documento;
    public $estado = 1;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Tipo_documentos_model", $campo)) {
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
        $rta = $this->db->insert('ges_tipos_documentales', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_tp_documento' => $this->db->insert_id()
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
        $this->db->where('ide_tipo_documental', $this->ide_tipo_documental);
        $rta = $this->db->update('ges_tipos_documentales', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_tp_documento' => $this->ide_tipo_documental
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
        $this->db->where('ide_tipo_documental', $codigo);
        $rta = $this->db->update('ges_tipos_documentales');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }

    public function getCombo($pqrds = false)
    {
        $query = $this->db->select('ide_tipo_documental as id,tipo_documental as nom, dias_tramite as nro_dias,dias_habiles as tp_dias')
            ->where('estado', 1);

        if ($pqrds) {
            $query = $query->where('pqrd', 1);
        }

        $query = $query->get('ges_tipos_documentales');

        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;

    }

    public function getComboPqrd($pqrds = false)
    {
        $query = $this->db->select('ide_tipo_documental as id,tipo_documental as nom, dias_tramite as nro_dias,dias_habiles as tp_dias')
            ->where('estado', 1);

        if ($pqrds) {
            $query = $query->where('pqrd', 1);
        }


        $query = $query->get('ges_tipos_documentales');
        $error = $this->error();
        $respuesta = [
            'data' => $error['code'] == 0 ? $query->result_array() : [],
            'error' => $error['code'] == 0 ? '' : $error,
            'msg' => ''
        ];
        return $respuesta;

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 27/08/2018
 * Time: 4:00 PM
 */

class Series_carpetas_model extends CI_Model
{
    public $ide_expediente;
    public $nombre_expediente;
    public $numero_expediente;
    public $observaciones;
    public $fecha_creacion;
    public $nom_usuario;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCombo()
    {
        $query = $this->db->select('ide_expediente as id,nombre_expediente as nom,numero_expediente as num, observaciones as obs, nom_usuario as usuario')
            ->get('ges_expedientes');

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
            if (property_exists("series_carpetas_model", $campo)) {
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
        $rta = $this->db->insert('ges_expedientes', $this);

        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_series' => $this->db->insert_id()
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
        $this->db->where('ide_expediente', $this->ide_expediente);
        $rta = $this->db->update('ges_expedientes', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_expediente' => $this->ide_expediente
            ];
        } else {
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar edicion del registro",
                'error_msg' => $this->error(),
                'error_num' => $this->error()
            ];
        }
        return $respuesta;
    }

    public function eliminar($codigo, $fechaDelete)
    {
        $this->db->set('estado', false);
        $this->db->set('fecha_delete', $fechaDelete);
        $this->db->where('ide_expediente', $codigo);
        $rta = $this->db->update('ges_expedientes');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }
}
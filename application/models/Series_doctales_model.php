<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 27/08/2018
 * Time: 4:00 PM
 */

class Series_doctales_model extends CI_Model
{
    public $ide_serie;
    public $cod_dependencia;
    public $cod_serie = 0;
    public $nombre_serie;
    public $registro_padre;
    public $nivel;
    public $estado = 1;
    public function __construct()
    {
        parent::__construct();
    }
    public function getCombo()
    {
        $query = $this->db->select('ide_serie as id,nombre_serie as nom')
            ->where('estado', 1)
            ->get('ges_series');

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
            if (property_exists("series_doctales_model", $campo)) {
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
        $rta = $this->db->insert('ges_series', $this);

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
        $this->db->where('ide_serie', $this->ide_serie);
        $rta = $this->db->update('ges_series', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_series' => $this->ide_serie
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
        $this->db->where('ide_serie', $codigo);
        $rta = $this->db->update('ges_series');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 14/09/2018
 * Time: 10:57 AM
 */

class Seguimiento_model extends CI_Model
{

    public $ide_gestion;
    public $ide_unidad_doctal;
    public $text_gestion;
    public $feccha_gestion;
    public $ide_usuario;
    public $nom_usuario;
    public $ide_anexo;
    public $ide_estado;
    public $raw_json;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Seguimiento_model", $campo)) {
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
        $rta = $this->db->insert('ges_gestion', $this);
        if ($rta) {
            $cd = $this->db->insert_id();
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'codigo' => $cd,
                'sql' => $this->db->last_query()
            ];
        } else {
            $error = $this->error();
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $error['message'],
                'error_num' => $error['code'],
                'sql' => $this->db->last_query()
            ];
        }
        return $respuesta;
    }

}
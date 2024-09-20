<?php
class Dependencias_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getCombo()
    {
        $query = $this->db->select('cod_dependencias as id,nombre_dependencia as nom')
            ->get('vwv_comun_dependencias');
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 10/09/2018
 * Time: 10:27 AM
 */

class Divipola_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getCombo()
    {
        $query = $this->db->select('ide_municipio as id,divipol as nom')
            // ->where("estado",1)
            ->distinct()
            ->get('vwv_municipios');

        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 29/08/2018
 * Time: 11:34 AM
 */

class Tipo_identificacion_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getCombo()
    {
        $query = $this->db->select('ide_tipo_identificacion as id,nom_tipo_identificacion as nom')
            ->get('vwv_comun_tipo_identificacion');
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }
}
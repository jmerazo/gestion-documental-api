<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 27/08/2018
 * Time: 11:32 AM
 */
class Cargos_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getCombo()
    {
        $query = $this->db->select('cod_cargo as id,nom_cargo as nom')
            ->order_by('nom_cargo')
            ->get('aux_cargos');
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

}
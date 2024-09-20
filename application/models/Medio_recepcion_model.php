<?php
/**
 * Copyright (c) 2018. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 27/08/2018
 * Time: 5:20 PM
 */
class Medio_recepcion_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
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
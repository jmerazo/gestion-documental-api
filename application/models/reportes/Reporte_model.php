<?php


class Reporte_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();

    }

    function obtenerDatosSql($sql)
    {
        $query = $this->db->query($sql);
        $datos = $query->result_array();
        $error = $this->db->error();
        return $error['code'] == 0 ? $datos : [];
    }
}
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
 * Date: 30/05/20
 * Time: 9:54 AM
 */
class Exception_model extends CI_Model
{
    public $ide_rol;
    public $nombre_rol;
    public $descripcion_rol;
    public $estado = 1;

    function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Roles_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        return $this->db->error();
    }

    public function get($id)
    {
        $query = $this->db->select('id,excepcion as nom, accion, consecutivo')
            ->where("id", $id)
            ->get('aux_excepciones_covid')
            ->row();
        return $query;
    }

    public function insert()
    {
        $rta = $this->db->insert('seg_roles', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_rol' => $this->db->insert_id()
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
        $this->db->where('ide_rol', $this->ide_rol);
        $rta = $this->db->update('seg_roles', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_rol' => $this->ide_rol
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
        $this->db->where('ide_rol', $codigo);
        $rta = $this->db->update('seg_roles');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }

    public function getCombo()
    {
        $query = $this->db->select('id,excepcion as nom, accion, consecutivo')
            ->where("activo", 'S')
            ->order_by('consecutivo', 'ASC')
            ->get('aux_excepciones_covid');

        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }
}
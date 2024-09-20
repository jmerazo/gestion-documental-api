<?php
/**
 * Created by PhpStorm.
 * User: dark_
 * Date: 23/05/2019
 * Time: 10:48
 */

class Anular_model extends CI_Model
{

    public $ide_radicado;
    public $fecha_registro;
    public $ide_radicado_anulado;
    public $fecha_anulado;
    public $motivo_anulado;
    public $cod_user;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Anular_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        return $this->db->error();
    }

    public function anularRadicado( $datos ){

        $anulado = $this->db->insert('rad_radicados_anulados', $datos);
        $error = $this->error();
        #var_dump($error);
        $respuesta = [
            'nom_arc' => $anulado,
            'error' => $error['code'] > 0 ? true : false,
            'msg' => '',
            'error_msg' => $error['message'],
            'error_num' => $error['code']
        ];

        return $respuesta;
    }

}
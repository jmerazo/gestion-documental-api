<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 13/08/2018
 * Time: 5:38 PM
 */

class Traza_documento_model extends CI_Model
{

    /*#campos de la tabla en la Bd*/


    public $ide_documento;
    public $cod_radicado;
    public $pk;
    public $fecha_traza;
    public $id_accion_traza;
    public $json_usuario;
    public $json_dts_accion;

    private $error_msg = null;
    private $error_nro = 0;


    public function __construct()
    {
        parent::__construct();
    }


    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Traza_documento_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        $error = $this->db->error();
        $this->error_msg = $error['message'];
        $this->error_nro = $error['code'];
    }

    public function insert()
    {
        $rta = $this->db->insert('ges_trazas_unidad_doctales', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_tp_anexo' => $this->db->insert_id()
            ];
        } else {
            $this->error();
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $this->error_msg,
                'error_num' => $this->error_nro
            ];
        }
        return $respuesta;
    }
}
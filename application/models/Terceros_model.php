<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 27/08/2018
 * Time: 2:42 PM
 */

class Terceros_model extends CI_Model
{

    public $ide_tercero;
    public $ide_tipo_identf;
    public $nit_tercero;
    public $tipo_tercero;
    public $nombres_tercero;
    public $apellidos_tercero;
    public $nom_entidad;
    public $tel_fijo_tercero;
    public $cel_fijo_tercero;
    public $direccion_tercero;
    public $mail_tercero;
    public $ide_divipola_tercero;
    public $estado = 1;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Terceros_model", $campo)) {
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
        $rta = $this->db->insert('rad_terceros', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_terceros' => $this->db->insert_id()
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
        $this->db->where('ide_tercero', $this->ide_tercero);
        $rta = $this->db->update('rad_terceros', $this);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'cd_terceros' => $this->ide_tercero
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
        $this->db->where('ide_tercero', $codigo);
        $rta = $this->db->update('rad_terceros');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }

    public function searchCombo($search)
    {
        //Busqueda especial
        $prex = '';
        if ($search) {
            $prex = 'IN BOOLEAN MODE';
        }

        /* $sql = "select ide_tercero as codigo,nit_tercero, dts_tercero, divipol, mail_tercero, nom_entidad, nom_apes,
 MATCH (nombres_tercero,apellidos_tercero,nom_entidad) AGAINST ('$search' $prex) as score from vwv_terceros
                 ORDER BY score DESC  limit 15";*/

        $sql = "select ide_tercero as codigo,nit_tercero, dts_tercero, divipol, mail_tercero, nom_entidad, 
nom_apes from vwv_terceros
                where CONCAT_WS(' ', dts_tercero,nom_apes, nom_entidad) LIKE '%${search}%'  limit 15";


        //echo $sql;

        $query = $this->db->query($sql);

        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

    /**
     * @param $tipoDoc Tipo de docuemnto del tercero
     * @param $nroDoc nro de docuemto del Tercero
     * @return array (Nombres y apellidos del tercero)
     */
    public function getSerarchXDocumento($tipoDoc, $nroDoc) // esta mal escrito el nombre de la funcion
    {
        $dts = [];

        $sql = 'SELECT ide_tercero, ide_tipo_identf, nit_tercero, tipo_tercero, nombres_tercero, apellidos_tercero, nom_entidad, tel_fijo_tercero
, cel_fijo_tercero, direccion_tercero, mail_tercero, ide_divipola_tercero
FROM rad_terceros WHERE ide_tipo_identf=? AND TRIM(nit_tercero)=? limit 1';


        $query = $this->db->query($sql, [$tipoDoc, $nroDoc]);
#        echo $this->db->last_query();
        $respuesta = [
            'data' => $query->row_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;


        return $dts;
    }

    public function getBuscarXDocumento($tipoDoc, $nroDoc)
    {
        //$dts = [];

        $sql = 'SELECT ide_tercero, ide_tipo_identf, nit_tercero, tipo_tercero, nombres_tercero, apellidos_tercero, nom_entidad, tel_fijo_tercero
, cel_fijo_tercero, direccion_tercero, mail_tercero, ide_divipola_tercero
FROM rad_terceros WHERE ide_tipo_identf=? AND TRIM(nit_tercero)=? limit 1';


        $query = $this->db->query($sql, [$tipoDoc, $nroDoc]);
#        echo $this->db->last_query();
        $respuesta = [
            'data' => $query->row_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;


        //return $dts;
    }
}
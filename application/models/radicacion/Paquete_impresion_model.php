<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 5/10/2018
 * Time: 10:58 AM
 */
defined('BASEPATH') OR exit('No se permite el acceso directo al script');

class Paquete_impresion_model extends CI_Model
{
    public $ide_impresion_radicado;
    public $numero_hoja;
    public $cod_radicado_inicio;
    public $cod_radicado_final;
    public $ide_tipo_radicado;
    public $fecha_impresion;
    public $ide_radicado_final;
    public $ide_radicado_inicio;
    public $nro_resgistros;
    public $origen;
    public $id_ventanilla;
    public $json_planilla;


    public function __construct()
    {
        parent::__construct();
        $this->origen = 'RADICACION';
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Paquete_impresion_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    public function insert()
    {
        $rta = $this->db->insert('rad_impresion_radicados', $this);
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

    /**
     * @param $cod_pq {int} codigo Primary key de la planilla
     * @return array arreglo de datos de los registros impresos en esta planilla
     */
    public function getPlanilla($cod_pq)
    {

        $row = $this->db->select('ide_radicado_inicio,ide_radicado_final,ide_tipo_radicado,json_planilla')
            ->where('ide_impresion_radicado', $cod_pq)
            ->get('rad_impresion_radicados')
            ->row(0);

        $respuesta = [
            'data' => json_decode($row->json_planilla, true),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

    private function error()
    {
        return $this->db->error();
    }

    public function eliminar($codigo)
    {
        $this->db->set('estado', false);
        $this->db->where('ide_impresion_radicado', $codigo);
        $rta = $this->db->update('rad_impresion_radicados');

        if ($rta) {
            return true;
        } else {
            $error = $this->db->error();
            throw new Exception($error['message'], $error['code']);
        }
    }

    /**
     * @return array
     */
    public function update($data)
    {
        $respuesta = array();
        $this->db->where('ide_impresion_radicado', $this->ide_impresion_radicado);
        $rta = $this->db->update('rad_impresion_radicados', $data);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro actualizado correctamente",
                'cd_paquete' => $this->ide_impresion_radicado
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

    /**
     * Permite optener el listado de documentos actualizados del paquete, para luego actulizar
     * @return array
     */
    public function getListPlanilla($tiporad, $radIni, $radFin, $codVentanilla)
    {
        $sql = 'SELECT ide_unidad_documental AS pk, cod_radicado AS nro_rad, asunto_unidad_documental AS asunto, fecha_radicado AS fec_rad, numero_documento_radicado AS nro_doc, fecha_unidad_documental AS fec_doc, nombre_unidad_adtiva_destino AS ofi_dest, datos_tercero_origen AS dts_tercero, 
(SELECT GROUP_CONCAT(CONCAT(\'{"nomapes":"\',nom_apes,\'"}\')) FROM `vwv_comun_funcionarios` f WHERE  FIND_IN_SET(f.`ide_funcionario` ,ges_unidad_documental.`func_responsables`) LIMIT 1) AS fun_dest, 
tipo_documental AS tip_doc, fecha_respuesta AS fec_rta 
FROM ges_unidad_documental  
WHERE ide_tipo_radicado = ' . $tiporad . ' AND activo_radicado = "S" AND ide_unidad_documental BETWEEN ' . $radIni . ' and ' . $radFin
            . ' and ide_ventanilla =' . $codVentanilla;


        $query = $this->db->query($sql);
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }
}
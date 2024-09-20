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
 * User: SysWork
 * Date: 27/08/2018
 * Time: 2:42 PM
 */
class Radicar_model extends CI_Model
{

    public $ide_radicado;
    public $ide_tipo_radicado;
    public $ide_tipo_documental;
    public $ide_medio_recepcion;
    public $fecha_documento_radicado;
    public $numero_documento_radicado;
    public $ide_radicado_respuesta;
    public $fecha_radicado;
    public $asunto_documento;
    public $numero_folios;
    public $fecha_respuesta;
    public $ide_tercero_origen;
    public $persona_elaboro;
    public $ide_unidad_administrativa_destino;
    public $ide_funcionario_destino;
    public $ide_unidad_administrativa_origen;
    public $ide_funcionario_origen;
    public $observaciones_radicado;
    public $ide_anexo_a;
    public $nom_usuario;
    public $fecha_registro;
    public $activo_radicado;
    public $nro_anexos;
    public $ide_ventanilla;
    public $mail_respuesta;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Radicar_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        return $this->db->error();
    }

    public function insertEntrada()
    {
        $rta = $this->db->insert('rad_radicados', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $cd = $this->db->insert_id();

            $dtsRadicado = $this->db->select('cod_radicado,pin_pqrd')
                ->where('ide_radicado', $cd)
                ->get('rad_radicados')
                ->row(0);
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'codigo' => $cd,
                'nro_radicado' => $dtsRadicado->cod_radicado,
                'pin_pqrd' => $dtsRadicado->pin_pqrd,
                'fec_rad' => $this->fecha_radicado,
                'fec_doc' => $this->fecha_documento_radicado,
                'nfol'=>$this->numero_folios,
                'nanx'=>$this->nro_anexos,
            ];
        } else {
            $error = $this->error();
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $error['message'],
                'error_num' => $error['code']
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

    public function updateFile($codigo, $data)
    {
        $this->db->where('ide_radicado', $codigo);
        $rta = $this->db->update('rad_radicados', $data);
        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro actualizado correctamente",
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
        $buscar = explode(" ", $search);

        $this->db->select("ide_tercero,nit_tercero,dts_tercero, divipol,mail_tercero");
        $this->db->like('dts_tercero', $search);

        foreach ($buscar as $v) {
            $this->db->or_like('dts_tercero', $v);
        }

        $query = $this->db->get('vwv_terceros');
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;

    }

    public function listPrintRadicados($cat)
    {

        $ultimoRad = $this->db->select_max('ide_radicado_final')
            ->where('ide_tipo_radicado', $cat)
            ->where('estado', true)
            ->where('origen', 'RADICACION')
            ->get('rad_impresion_radicados')
            ->row(0)->ide_radicado_final;

        $ultimoRad = !empty($ultimoRad) ? $ultimoRad : 0;
        $this->db->reset_query();

        $this->db->select("ide_radicado as pk,cod_radicado as nro_rad,asunto_documento as asunto,fecha_radicado as fec_rad,
        numero_documento_radicado as nro_doc, fecha_documento_radicado as fec_doc, nom_unidad_administrativa_destino as ofi_dest, 
        nom_tercero_origen as dts_tercero, nom_funcionario_destino as fun_dest,nom_tipo_documental as tip_doc,fecha_respuesta as fec_rta");

        $this->db->where(['ide_tipo_radicado' => $cat, 'activo_radicado' => 'S', 'ide_radicado >' => $ultimoRad]);

        $query = $this->db->get('rad_radicados');
        #echo '---' . $this->db->last_query();
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

    public function getRadicado($codigoRadicado)
    {
        $query = $this->db->select(
            'ide_radicado as pk,cod_radicado,ide_tipo_radicado , nom_tipo_documental, nom_medio_recepcion, fecha_documento_radicado
            , numero_documento_radicado, fecha_radicado, numero_folios
            , asunto_documento, fecha_respuesta, nom_tercero_origen
            , persona_elaboro, nom_unidad_administrativa_origen, nom_unidad_administrativa_destino
            , nom_funcionario_origen, nom_funcionario_destino, observaciones_radicado
            , fecha_registro, activo_radicado
            , nom_archivo, size_file')
            ->where('ide_radicado', $codigoRadicado)
            ->get('rad_radicados');

        $error = $this->error();
        #var_dump($error);
        $respuesta = [
            'data' => $query->row_array(),
            'error' => $error['code'] > 0 ? true : false,
            'msg' => '',
            'error_msg' => $error['message'],
            'error_num' => $error['code']
        ];

        return $respuesta;
    }
    public function getRadicadoByNroRad($nroRadicado)
    {
        $query = $this->db->select(
            'ide_radicado as pk,cod_radicado,ide_tipo_radicado , nom_tipo_documental, nom_medio_recepcion, fecha_documento_radicado
            , numero_documento_radicado, fecha_radicado, numero_folios
            , asunto_documento, fecha_respuesta, nom_tercero_origen
            , persona_elaboro, nom_unidad_administrativa_origen, nom_unidad_administrativa_destino
            , nom_funcionario_origen, nom_funcionario_destino, observaciones_radicado
            , fecha_registro, activo_radicado
            , nom_archivo, size_file')
            ->where('cod_radicado', $nroRadicado)
            ->get('rad_radicados');

        $error = $this->error();
        $respuesta = [
            'data' => $query->row_array(),
            'error' => $error['code'] > 0 ? true : false,
            'msg' => '',
            'error_msg' => $error['message'],
            'error_num' => $error['code']
        ];

        return $respuesta;
    }

    public function getNombreArchivo($codRadicado)
    {
        $archivo = $this->db->select(
            'nom_archivo')
            ->where('ide_radicado', $codRadicado)
            ->get('rad_radicados')
            ->row(0)->nom_archivo;


        $error = $this->error();
        #var_dump($error);
        $respuesta = [
            'nom_arc' => $archivo,
            'error' => $error['code'] > 0 ? true : false,
            'msg' => '',
            'error_msg' => $error['message'],
            'error_num' => $error['code']
        ];

        return $respuesta;
    }

}
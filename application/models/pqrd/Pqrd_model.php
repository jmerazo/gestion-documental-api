<?php

/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 16/03/19
 * Time: 9:52 AM
 */
class Pqrd_model extends CI_Model
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

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Pqrd_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    public function insertEntrada()
    {
        $rta = $this->db->insert('rad_radicados', $this);
        #echo '---'.$this->db->last_query();
        if ($rta) {
            $cd = $this->db->insert_id();
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'codigo' => $cd,
                'nro_radicado' => $this->db->select('cod_radicado')
                    ->where('ide_radicado', $cd)
                    ->get('rad_radicados')
                    ->row(0)->cod_radicado,

                'fec_rad' => $this->fecha_radicado,
                'fec_doc' => $this->fecha_documento_radicado,
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

    /**
     * @param $ideRad
     * @param $pinPqrd
     * @return array
     */
    public function getPqrd($ideRad, $pinPqrd)
    {

        //$sql="SELECT * FROM prointel_ptelptyo.rad_radicados WHERE TRIM(cod_radicado) = ? AND TRIM(pin_pqrd) = ? limit 1";

        $sql=  "SELECT  
     `ges_unidad_documental`.`cod_radicado`
    , `ges_unidad_documental`.`fecha_limite_respuesta`
    , `ges_unidad_documental`.`tipo_documental`
    , `ges_unidad_documental`.`medio_recepcion`
    , `ges_unidad_documental`.`fecha_unidad_documental` 
    , `ges_unidad_documental`.`numero_documento_radicado`
    , `ges_unidad_documental`.`fecha_radicado`
    , `ges_unidad_documental`.`asunto_unidad_documental`
    , `ges_unidad_documental`.`numero_folios`
    , `ges_unidad_documental`.`datos_tercero_origen`
    , `ges_unidad_documental`.`nombre_unidad_adtiva_destino`
    , `ges_unidad_documental`.`nombre_funcionario_destino`
    , `ges_unidad_documental`.`observaciones_unidad_documental`
    , `ges_unidad_documental`.`nro_anexos`
    ,  `ges_gestion`.`text_gestion`
    , `ges_gestion`.`feccha_gestion`
    , `ges_gestion`.`nom_usuario`
 FROM
	`admin_prointel_ptelptyo`.`ges_unidad_documental`
          LEFT JOIN `admin_prointel_ptelptyo`.`ges_gestion` 
        ON (`ges_gestion`.`ide_unidad_doctal` = `ges_unidad_documental`.`ide_unidad_documental`) where cod_radicado=?  and pin_pqrd=?";

        $query = $this->db->query($sql, [$ideRad, $pinPqrd]);
        #echo $this->db->last_query();
        $respuesta = [
            'data' => $query->row_array(),
            'error' => false,
            'msg' => ''
        ];

        return $respuesta;

    }

    /**
     * @return array
     */

    public function getTiposDocs()
    {

        $sql="SELECT ide_tipo_documental FROM admin_prointel_ptelptyo.rad_radicados";

        $query = $this->db->query($sql);
#        echo $this->db->last_query();
        $respuesta = [
            'data' => $query->result_array(),
            /*'data' => $query->row_array(), row envia una sola fila */
            'error' => false,
            'msg' => ''
        ];

        return $respuesta;

    }

}
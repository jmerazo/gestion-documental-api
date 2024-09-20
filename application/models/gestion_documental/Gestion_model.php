<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 14/09/2018
 * Time: 10:57 AM
 */

class Gestion_model extends CI_Model
{
    public $ide_unidad_documental;
    public $ide_radicado;
    PUBLIC $cod_radicado;
    public $ide_tipo_radicado;
    public $ide_tipo_documental;
    public $tipo_documental;
    public $ide_medio_recepcion;
    public $medio_recepcion;
    public $fecha_unidad_documental;
    public $numero_documento_radicado;
    public $ide_radicado_respuesta;
    public $fecha_radicado;
    public $ide_funcionario_origen;
    public $nombre_funcionario_origen;
    public $asunto_unidad_documental;
    public $numero_folios;
    public $fecha_respuesta;
    public $ide_tercero_origen;
    public $datos_tercero_origen;
    public $ide_unidad_adtiva_origen;
    public $nombre_unidad_adtiva_origen;
    public $fecha_limite_respuesta;
    public $persona_elaboro;
    public $ide_unidad_adtiva_destino;
    public $nombre_unidad_adtiva_destino;
    public $ide_funcionario_destino;
    public $nombre_funcionario_destino;
    public $observaciones_unidad_documental;
    public $ide_anexo_a;
    public $nom_usuario;
    public $fecha_registro;
    public $activo_radicado;
    public $nro_anexos;
    public $ide_expediente;
    public $ide_serie;
    public $tipo_radicado;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Gestion_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        return $this->db->error();
    }

    public function getInfoDocumento($codigoRadicado)
    {
        $query = $this->db->select(
            'ide_unidad_documental as pk,
            cod_radicado,
            ide_tipo_radicado,
            tipo_documental,
            medio_recepcion,
            fecha_unidad_documental as fecha_documento_radicado,
            numero_documento_radicado,
            fecha_radicado,
            numero_folios,
            asunto_unidad_documental as asunto_documento,
            fecha_respuesta,
            datos_tercero_origen as nom_tercero_origen,
            persona_elaboro,
            nombre_unidad_adtiva_origen as nom_unidad_administrativa_origen,
            nombre_unidad_adtiva_destino as nom_unidad_administrativa_destino,
            nombre_funcionario_origen as nom_funcionario_origen,
            nombre_funcionario_destino as nom_funcionario_destino,
            observaciones_unidad_documental as observaciones_radicado,
            fecha_registro,
            activo_radicado,
            tipo_documental as nom_tipo_documental,
            JSON_OBJECT("tel", tel_fijo_tercero, "cel", cel_fijo_tercero, "mail", mail_tercero, "dir", direccion_tercero) AS ots_dts_tercero,
            nom_archivo,
            size_file,
            nro_anexos,
            fecha_limite_respuesta as fecha_limite,
            (SELECT divipol FROM vwv_municipios WHERE ide_municipio = rad_terceros.ide_divipola_tercero LIMIT 1) AS nom_divipol,
            IF(mail_respuesta IS NULL OR mail_respuesta = "", mail_tercero, mail_respuesta) AS mail_respuesta' // Corrección
        )
        ->where('ide_unidad_documental', $codigoRadicado)
        ->get('ges_unidad_documental LEFT JOIN rad_terceros ON (ges_unidad_documental.ide_tercero_origen = rad_terceros.ide_tercero)');

        //echo $this->db->last_query();
        $error = $this->error();
        
        $respuesta = [
            'data' => $error['code'] == 0 ? $query->row_array() : null,
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
            ->where('ide_unidad_documental', $codRadicado)
            ->get('ges_unidad_documental')
            ->row(0)->nom_archivo;


        $error = $this->error();

        $respuesta = [
            'nom_arc' => $archivo,
            'error' => $error['code'] > 0 ? true : false,
            'msg' => '',
            'error_msg' => $error['message'],
            'error_num' => $error['code']
        ];

        return $respuesta;
    }

    /*
     *
     * @cat: Variable que indica la categoria (Tipo de radicados) de los documentos a imprimir;
     * @return :array con los campos ['data'=>array(),'error'=>bool, 'msg'=>String'']
     */

    public function listPrintRadicados($cat)
    {

        $ultimoRad = $this->db->select_max('ide_radicado_final')
            ->where('ide_tipo_radicado', $cat)
            ->where('estado', true)
            ->where('origen', 'GESTION')
            ->where('id_ventanilla', $this->cnfUser->ventanilla)
            ->get('rad_impresion_radicados')
            ->row(0)->ide_radicado_final;

        #echo '---' . $this->db->last_query();
        $ultimoRad = !empty($ultimoRad) ? $ultimoRad : 0;
        $this->db->reset_query();

        $sql = 'SELECT ide_unidad_documental AS pk, cod_radicado AS nro_rad, asunto_unidad_documental AS asunto, fecha_radicado AS fec_rad, numero_documento_radicado AS nro_doc, fecha_unidad_documental AS fec_doc, nombre_unidad_adtiva_destino AS ofi_dest, datos_tercero_origen AS dts_tercero, 
(SELECT GROUP_CONCAT(CONCAT(\'{"nomapes":"\',nom_apes,\'"}\')) FROM `vwv_comun_funcionarios` f WHERE  FIND_IN_SET(f.`ide_funcionario` ,ges_unidad_documental.`func_responsables`) LIMIT 1) AS fun_dest, 
tipo_documental AS tip_doc, fecha_respuesta AS fec_rta 
FROM ges_unidad_documental  
WHERE ide_tipo_radicado = ' . $cat . ' AND activo_radicado = "S" AND ide_unidad_documental >' . $ultimoRad
            . ' and ide_ventanilla =' . $this->cnfUser->ventanilla;

        $query = $this->db->query($sql);
        //echo '---' . $this->db->last_query();
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

    public function getTrazaDocumento($codRadicado = 10361)
    {

        /*$sql = "SELECT JSON_UNQUOTE(IFNULL(JSON_EXTRACT(json_usuario, '$.us_nom_apes'),JSON_EXTRACT(json_usuario, '$.us'))) quien,
aux_estado_trazas.nom_estado_traza que, 
fecha_traza cuando, 
IF(ISNULL( IF(ISNULL(JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.observaciones'))), 
JSON_UNQUOTE(JSON_EXTRACT(json_dts_accion, '$.nomapes')), 
JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.observaciones'))
)),(SELECT text_gestion FROM `ges_gestion` WHERE ges_gestion.`ide_gestion`=ges_trazas_unidad_doctales.`pk`),NULL) observaciones,
SUBSTRING_INDEX(RIGHT(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.raw_json_file')),'client_name',-1) ,LENGTH(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.raw_json_file')),'client_name',-1))-5), '\"',1) archivo 
FROM ges_trazas_unidad_doctales 
INNER JOIN aux_estado_trazas ON aux_estado_trazas.cod_estado_traza = ges_trazas_unidad_doctales.id_accion_traza 
WHERE ide_documento=$codRadicado order by fecha_traza desc ";*/

        $sql = "SELECT JSON_UNQUOTE(
	IF(
	(ISNULL(JSON_EXTRACT(json_usuario, '$.us_nom_apes')) OR LENGTH(JSON_EXTRACT(json_usuario, '$.us_nom_apes'))<3),
	JSON_EXTRACT(json_usuario, '$.us'),
JSON_EXTRACT(json_usuario, '$.us_nom_apes')
	)
) quien, 
aux_estado_trazas.nom_estado_traza que, 
fecha_traza cuando, 
IF(ISNULL(JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.observaciones'))), JSON_UNQUOTE(JSON_EXTRACT(json_dts_accion, '$.nomapes')), JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.observaciones'))) observaciones, 
SUBSTRING_INDEX(RIGHT(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.raw_json_file')),'client_name',-1) ,LENGTH(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(json_usuario, '$.raw_json_file')),'client_name',-1))-5), '\"',1) archivo 
FROM ges_trazas_unidad_doctales 
INNER JOIN aux_estado_trazas ON aux_estado_trazas.cod_estado_traza = ges_trazas_unidad_doctales.id_accion_traza 
WHERE ide_documento=$codRadicado order by fecha_traza desc ";


        $error = $this->error();

        $query = $this->db->query($sql);
        //echo '---' . $this->db->last_query();
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
           // 'sql' => $this->db->last_query(),
            'msg' => ''
        ];
        return $respuesta;
    }

    public function getIdFuncionariosDependencia($oficina)
    {
        $sql = "SELECT  GROUP_CONCAT(DISTINCT adm_personas.ide_funcionario) AS id
                    FROM adm_personas
                      INNER JOIN aux_cargos 
                        ON (adm_personas.cod_cargo = aux_cargos.cod_cargo)
                     JOIN (WITH RECURSIVE dependencias_recursiva 
                        AS (
                              SELECT cod_dependencias, nombre_dependencia FROM adm_dependencias 
				WHERE padre= $oficina OR cod_dependencias=$oficina
                              UNION ALL
                              SELECT d.cod_dependencias, d.nombre_dependencia FROM adm_dependencias d 
                             JOIN dependencias_recursiva ON dependencias_recursiva.cod_dependencias = d.padre
                            )
                        SELECT cod_dependencias
                            FROM dependencias_recursiva) t ON t.cod_dependencias = adm_personas.cod_dependencia
                            WHERE adm_personas.`activo`='S'";

        $query = $this->db->query($sql);
        $error = $this->error();

        //echo '---' . $this->db->last_query();
        $respuesta = [
            'data' => $query->num_rows() > 0 ? explode(',', $query->row()->id) : [],
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;

    }

}
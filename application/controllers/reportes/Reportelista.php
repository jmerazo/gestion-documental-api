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
 * Date: 15/02/20
 * Time: 10:12 AM
 */
class Reportelista extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        //$this->auth();
        $this->load->database();
        $this->load->model('gestion_documental/gestion_model', 'bdGestion');
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function exportarxls_get($filtro, $token)
    {
        $filtro = base64_decode($filtro);
        $token = base64_decode($token);
        $filtro = json_decode($filtro, true);

        //var_dump($filtro);
        # exit;

        $this->auth($token);

        $this->load->helper('paginador_helper');
        $cmps = ['ges_unidad_documental.ide_unidad_documental as pk', 'cod_radicado', 'fecha_radicado as fec_doc_rad',
            "DATE_FORMAT(fecha_limite_respuesta,'%d-%m-%Y') AS fecha_limite_respuesta",
            "DATE_FORMAT((SELECT ges_trazas_unidad_doctales.fecha_traza FROM ges_trazas_unidad_doctales WHERE ges_unidad_documental.ide_unidad_documental = ges_trazas_unidad_doctales.ide_documento AND ges_trazas_unidad_doctales.id_accion_traza = 111 ORDER BY cod_traza DESC LIMIT 1 ),'%d-%m-%Y') as fecha_respuesta",
            "IF(NOT ISNULL(fecha_limite_respuesta),IF(NOT ISNULL((SELECT ges_trazas_unidad_doctales.fecha_traza FROM ges_trazas_unidad_doctales WHERE ges_unidad_documental.ide_unidad_documental = ges_trazas_unidad_doctales.ide_documento AND ges_trazas_unidad_doctales.id_accion_traza = 111 ORDER BY cod_traza DESC LIMIT 1)),DATEDIFF(fecha_limite_respuesta,(SELECT ges_trazas_unidad_doctales.fecha_traza FROM ges_trazas_unidad_doctales WHERE ges_unidad_documental.ide_unidad_documental = ges_trazas_unidad_doctales.ide_documento AND ges_trazas_unidad_doctales.id_accion_traza = 111 ORDER BY cod_traza DESC LIMIT 1)),DATEDIFF(`fecha_limite_respuesta`,NOW())), 0)  dias_diferencia",
            'medio_recepcion as medio_recepcion',
            'numero_documento_radicado as nro_doc',
            'asunto_unidad_documental as asunto', 'fecha_radicado', 'datos_tercero_origen as dts_tercero',
            'JSON_OBJECT("tel",tel_fijo_tercero,"cel",cel_fijo_tercero,"mail",mail_tercero,"dir",direccion_tercero) AS ots_dts_tercero',
            'nombre_unidad_adtiva_origen as nom_off_origen',
            'nombre_unidad_adtiva_destino as nom_off_destino', 'ide_tipo_radicado as tipo_rad',
            'nombre_funcionario_destino nom_fun_destino',
            'nombre_funcionario_origen nom_fun_origen', 'tipo_documental as tipo_doc', 'fecha_unidad_documental as fec_doc',
            'ges_unidad_documental.cod_esatdo', 'color AS color_esatdo', 'nombre_estado', 'cod_esatdo cod_estado', 'nom_archivo', 'nro_anexos, numero_folios',
            'FORMAT(100-(DATEDIFF(fecha_limite_respuesta,NOW())*100/dias_respuesta),0) AS porcentaje',
            'func_responsables',
            '(SELECT fecha_lectura FROM ges_radicados_funcionarios WHERE ges_radicados_funcionarios.ide_unidad_documental=pk AND ges_radicados_funcionarios.ide_funcionario_lector= ' . $this->certAut->codFun . ') AS leido',
            '(SELECT GROUP_CONCAT(DISTINCT adm_dependencias.nombre_dependencia SEPARATOR " | ") FROM adm_personas INNER JOIN adm_dependencias  ON (adm_personas.cod_dependencia = adm_dependencias.cod_dependencias)        WHERE FIND_IN_SET(adm_personas.ide_funcionario,func_responsables) LIMIT 1 ) AS dependencias_responsables',
            '(SELECT GROUP_CONCAT(DISTINCT  CONCAT_WS(" ", adm_personas.nombre1_funcionario,  adm_personas.nombre2_funcionario , adm_personas.apellido1_funcionario, adm_personas.apellido2_funcionario)  SEPARATOR " | ") FROM adm_personas INNER JOIN adm_dependencias ON (adm_personas.cod_dependencia = adm_dependencias.cod_dependencias) WHERE FIND_IN_SET(adm_personas.ide_funcionario,func_responsables) LIMIT 1 ) AS funcionarios_responsables'
        ];

        $nroPagina = 1;
        $campoOrden = 'fecha_radicado';
        $orden = 'DESC';
        $parambus = isset($filtro['parambus']) || null;

        if ($parambus && empty($parambus['valbus'])) {
            $parambus = null;
        }


        $filTc = @$filtro['tiptc']; //categoria del radicado, 1=entrada, 2=salida 3 interno
        $filTd = @$filtro['tiptd']; //Tipo docto radicado
        $otrosFiltros = @$filtro['otrofil']; //otros filtros
        $porEstado = @$filtro['por_estado']; //otros filtros
        $fechaInicio = @$filtro['fecInicio'];
        $fechaFin = @$filtro['fecFin'];
        $filOficina=@$filtro['oficina'];


        //$orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion = "activo_radicado = 'S' and YEARWEEK(ges_unidad_documental.fecha_radicado)>9";

        if (!empty($fechaFin) && !empty($fechaInicio)) {
            $condicion .= " AND date(fecha_radicado) BETWEEN '${fechaInicio}' AND '${fechaFin}' ";
        }

        #echo $otrosFiltros;
        if (!empty($otrosFiltros)) {
            switch ($otrosFiltros) {
                case 'responsable':
                    $condicion .= ' AND (FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';
                    #$condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';
                    break;
                case 'rad - devueltos':
                    $condicion .= " AND cod_esatdo<>11 and (cod_esatdo=10 or cod_esatdo=16) ";
                    break;
                case 'vencidos':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' AND DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) <1 ";
                    break;
                case '1 - 2':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' and DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) BETWEEN 1 AND 2 ";
                    break;
                case '3 - 5':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' AND DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) BETWEEN 3 AND 5  ";
                    break;
                case '6 - 10':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' AND DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) BETWEEN 6 AND 10 ";
                    break;
                case 'M10':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' AND DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) >10 ";
                    break;
            }
        }

        if (!empty($porEstado)) {
            if ($porEstado != 1) {
                if ($porEstado != '99x') {
                    $condicion .= "and cod_esatdo<>1  and cod_esatdo=$porEstado ";
                }

            } elseif ($porEstado == 1) {
                $condicion .= "  and cod_esatdo=$porEstado ";
            }
        }

        $tblsAdicional = '';
        if (!empty($filOficina)) {
            $tblsAdicional = "INNER JOIN ges_radicados_funcionarios ON(ges_unidad_documental.ide_unidad_documental=ges_radicados_funcionarios.ide_unidad_documental AND ges_radicados_funcionarios.ide_depen_funcionario=$filOficina)";
        }


        #Si el el rol es de ventanilla y  tiene una ventanilla asignada
        if ($this->certAut->ide_rol && isset($this->cnfUser->ventanilla) && !empty($this->cnfUser->ventanilla)) {
            $condicion .= ' and (ide_ventanilla = ' . $this->cnfUser->ventanilla . " or ide_ventanilla=99 ) ";
        }


        if ($this->cnfUser->filtro && $this->cnfUser->filtro !== 3) {

            #Solo puede ver los asignados o enviados por el funcionario en el modulo de radicacion
            #crear trigger para cuando se agregue un radicado cree una asignacion principal
            #crear trigguer para cuando se asigne(elimine) un documento a un funcionario actualice un campo en la unidad documenta, donde se almacene todos los codigos de funcionarios q pueden ver el documento

            if ($this->cnfUser->filtro && $this->cnfUser->filtro == 1) { //solo los asignados
                #la condicion es si esta como funcionario lector
                $condicion .= ' AND (FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';
                #$condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';

            } elseif (isset($this->cnfUser->oficinas) && count($this->cnfUser->oficinas) > 0) {

                $condicion .= ' and ((FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';

                //traigo todos los funcionarios q forman parte del area para luego armar la condicion
                $codFuns = $this->bdGestion->getIdFuncionariosDependencia($this->cnfUser->oficinas[0]);

                foreach ($codFuns['data'] as $r) {
                    $condicion .= ' OR FIND_IN_SET("' . $r . '", func_responsables) ';
                }


                #$condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';

                $condicion .= ' ) ';
            }

        }


        if (!empty($filTc)) {
            $condicion .= ' and ide_tipo_radicado = ' . (int)$filTc;
        }
        if (!empty($filTd)) {
            $condicion .= ' and ide_tipo_documental = ' . (int)$filTd;
        }

        $tbls = 'ges_unidad_documental 
                    INNER JOIN doc_estados ON(ges_unidad_documental . cod_esatdo = doc_estados . ide_estado) 
                    LEFT JOIN rad_terceros ON(ges_unidad_documental . ide_tercero_origen = rad_terceros . ide_tercero)
                    ' . $tblsAdicional;

        $data = paginar_todos($tbls, $cmps, null, $parambus, $condicion,
            $nroPagina, 100000, $campoOrden, $orden);

        //echo $data['sql'];
        //exit;
        $datax = [];
        foreach ($data['rows'] as $reg) {
            $reg['dts_tercero'] = json_decode($reg['dts_tercero'], true);
            $reg['ots_dts_tercero'] = json_decode($reg['ots_dts_tercero'], true);
            $reg['func_responsables'] = explode(",", $reg['func_responsables']);
            $reg['is_file'] = !empty($reg['nom_archivo']) ? true : false;
            $datax[] = $reg;
        }
        $data['rows'] = $datax;
        $this->respuesta = $data;

        $this->load->helper('Replistgral_xls_helper');

        repListgral($data['rows']);

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

}
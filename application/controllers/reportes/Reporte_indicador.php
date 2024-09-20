<?php

class Reporte_indicador extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        //$this->auth();
        $this->load->database();
        $this->load->model('reportes/reporte_model', 'mdReportes');
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function exportarxls_get($filtro, $token)
    {
        $filtro = base64_decode($filtro);
        $token = base64_decode($token);
        $filtro = json_decode($filtro, true);

        //var_dump($filtro);
        // exit;

        $this->auth($token);

        $this->load->helper('paginador_helper');
        $cmps = [
            'cod_radicado as radicado',
            'DATE_FORMAT(fecha_radicado, "%d-%m-%Y") AS fecha_radicado',
            'medio_recepcion as medio_recepcion',
            'asunto_unidad_documental AS asunto',
            "(SELECT JSON_UNQUOTE(JSON_EXTRACT(raw_json, '$.nombre_dependencia')) FROM ges_radicados_funcionarios WHERE ges_radicados_funcionarios.ide_unidad_documental = ges_unidad_documental.ide_unidad_documental ORDER BY ide_radicados_leidos DESC LIMIT 1) oficina_destino",
            "(SELECT JSON_UNQUOTE(JSON_EXTRACT(raw_json, '$.nomapes')) FROM ges_radicados_funcionarios WHERE ges_radicados_funcionarios.ide_unidad_documental = ges_unidad_documental.ide_unidad_documental ORDER BY ide_radicados_leidos DESC LIMIT 1) as funcionario_destino ",
            "ges_unidad_documental.tipo_documental AS tipo_documento",
            "fecha_unidad_documental AS fecha_documento",
            "doc_estados.nombre_estado as estado",
            "DATE_FORMAT(fecha_limite_respuesta,'%d-%m-%Y') AS fecha_limite_respuesta",
            "DATE_FORMAT((SELECT ges_trazas_unidad_doctales.fecha_traza FROM ges_trazas_unidad_doctales WHERE ges_unidad_documental.ide_unidad_documental = ges_trazas_unidad_doctales.ide_documento AND ges_trazas_unidad_doctales.id_accion_traza = 111 ORDER BY cod_traza DESC LIMIT 1 ),'%d-%m-%Y') as fecha_respuesta",
            "IF(NOT ISNULL(fecha_limite_respuesta),IF(NOT ISNULL((SELECT ges_trazas_unidad_doctales.fecha_traza FROM ges_trazas_unidad_doctales WHERE ges_unidad_documental.ide_unidad_documental = ges_trazas_unidad_doctales.ide_documento AND ges_trazas_unidad_doctales.id_accion_traza = 111 ORDER BY cod_traza DESC LIMIT 1)),DATEDIFF(fecha_limite_respuesta,(SELECT ges_trazas_unidad_doctales.fecha_traza FROM ges_trazas_unidad_doctales WHERE ges_unidad_documental.ide_unidad_documental = ges_trazas_unidad_doctales.ide_documento AND ges_trazas_unidad_doctales.id_accion_traza = 111 ORDER BY cod_traza DESC LIMIT 1)),DATEDIFF(`fecha_limite_respuesta`,NOW())), 0)  dias_diferencia"
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
        $fechaInicio = substr(@$filtro['fecInicio'], 0, 10);
        $fechaFin = substr(@$filtro['fecFin'], 0, 10);
        $filOficina = @$filtro['oficina'];


        //$orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion = "activo_radicado = 'S' ";

        if (!empty($fechaFin) && !empty($fechaInicio)) {
            $condicion .= " AND date(fecha_radicado) BETWEEN '${fechaInicio}' AND '${fechaFin}' ";
        }

        #echo $otrosFiltros;
        if (!empty($otrosFiltros)) {
            switch ($otrosFiltros) {
                case 'responsable':
                    $condicion .= ' AND (FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';
                    $condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';
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
                $condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';

            } elseif (isset($this->cnfUser->oficinas) && count($this->cnfUser->oficinas) > 0) {

                $condicion .= ' and ((FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';

                //traigo todos los funcionarios q forman parte del area para luego armar la condicion
                $codFuns = $this->bdGestion->getIdFuncionariosDependencia($this->cnfUser->oficinas[0]);

                foreach ($codFuns['data'] as $r) {
                    $condicion .= ' OR FIND_IN_SET("' . $r . '", func_responsables) ';
                }


                $condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';

                $condicion .= ' ) ';
            }

        }


        if (!empty($filTc)) {
            $condicion .= ' and ide_tipo_radicado = ' . (int)$filTc;
        }
        if (!empty($filTd)) {
            $condicion .= ' and ges_tipos_documentales.ide_tipo_documental = ' . (int)$filTd;
        }

        $tbls = 'ges_unidad_documental 
                    INNER JOIN doc_estados ON(ges_unidad_documental . cod_esatdo = doc_estados . ide_estado) 
                    LEFT JOIN rad_terceros ON(ges_unidad_documental . ide_tercero_origen = rad_terceros . ide_tercero)
                    LEFT JOIN ges_tipos_documentales ON (ges_tipos_documentales.ide_tipo_documental= ges_unidad_documental.ide_tipo_documental) 
                    ' . $tblsAdicional;

        $sql = "SELECT " . implode(",", $cmps) . " FROM $tbls " . " WHERE " . $condicion;

        //  echo  $sql;
        //exit();
        $data = $this->mdReportes->obtenerDatosSql($sql);

//        var_dump($data);
//        exit();
        $this->load->helper('RepIndicadores_xls_helper');

        repIndicadores($data);
        exit;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

}



<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 14/09/2018
 * Time: 10:55 AM
 */

class Gestion extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('gestion_documental/gestion_model', 'bdGestion');
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function paginar_post()
    {
        $this->auth();
        $this->load->helper('paginador_helper');
        $cmps = ['ges_unidad_documental.ide_unidad_documental as pk', 'cod_radicado', 'fecha_radicado as fec_doc_rad',
            'numero_documento_radicado as nro_doc',
            'asunto_unidad_documental as asunto', 'fecha_radicado', 'datos_tercero_origen as dts_tercero',
            'JSON_OBJECT("tel",tel_fijo_tercero,"cel",cel_fijo_tercero,"mail",mail_tercero,"dir",direccion_tercero) AS ots_dts_tercero',
            'nombre_unidad_adtiva_origen as nom_off_origen',
            'nombre_unidad_adtiva_destino as nom_off_destino', 'ide_tipo_radicado as tipo_rad',
            'nombre_funcionario_destino nom_fun_destino',
            'nombre_funcionario_origen nom_fun_origen', 'ges_unidad_documental.tipo_documental as tipo_doc', 'fecha_unidad_documental as fec_doc',
            'ges_unidad_documental.cod_esatdo', 'color AS color_esatdo', 'nombre_estado', 'cod_esatdo cod_estado', 'nom_archivo', 'nro_anexos, numero_folios',
            'FORMAT(100-(DATEDIFF(fecha_limite_respuesta,NOW())*100/dias_respuesta),0) AS porcentaje',
            'func_responsables', 'fecha_limite_respuesta as fecha_limite',
            '(SELECT fecha_lectura FROM ges_radicados_funcionarios WHERE ges_radicados_funcionarios.ide_unidad_documental=pk AND ges_radicados_funcionarios.ide_funcionario_lector= ' . $this->certAut->codFun . ' limit 1) AS leido',
            'ges_tipos_documentales.respuesta rec_rta'
        ];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        if ($parambus && empty($parambus['valbus'])) {
            $parambus = null;
        }


        $filTc = $this->input->post('tiptc'); //categoria del radicado, 1=entrada, 2=salida 3 interno
        $filTd = $this->input->post('tiptd'); //Tipo docto radicado

        $fechaInicio = $this->input->post('fecInicio');
        $fechaFin = $this->input->post('fecFin');

        $filOficina = (int)$this->input->post('oficina'); //Filtro por Oficina


        $otrosFiltros = $this->input->post('otrofil'); //otros filtros
        $porEstado = $this->input->post('por_estado'); //otros filtros


        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion = "activo_radicado = 'S' and YEARWEEK(ges_unidad_documental.fecha_radicado)>9";


        if (!empty($fechaFin) && !empty($fechaInicio)) {
            $condicion .= " AND date(fecha_radicado) BETWEEN '${fechaInicio}' AND '${fechaFin}'";
        }


        if (!empty($otrosFiltros)) {
            switch ($otrosFiltros) {
                case 'responsable':
                    $condicion .= ' AND (FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';
                    $condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';
                    break;
                case 'rad-devueltos':
                    $condicion .= " AND cod_esatdo<>11 and cod_esatdo=10 ";
                    break;
                case 'vencidos':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' AND DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) <1 ";
                    break;
                case '1-2':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' and DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) BETWEEN 1 AND 2 ";
                    break;
                case '3-5':
                    $condicion .= " AND cod_esatdo<>11 AND ges_unidad_documental.fecha_radicado>'" . FECHA_INIT_DATOS . "' AND DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) BETWEEN 3 AND 5  ";
                    break;
                case '6-10':
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
                    $condicion .= " and cod_esatdo<>1  and cod_esatdo=$porEstado ";
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
            $ventanillaPublicaWeb = $this->cnfUser->ventanilla == 1 ? " or ide_ventanilla=99" : '';
            $condicion .= ' and ( ide_ventanilla=' . $this->cnfUser->ventanilla . $ventanillaPublicaWeb . " ) ";
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
            $condicion .= ' and ide_tipo_radicado=' . (int)$filTc;
        }
        if (!empty($filTd)) {
            $condicion .= ' and ges_tipos_documentales.ide_tipo_documental=' . (int)$filTd;
        }

        $tbls = 'ges_unidad_documental 
                    INNER JOIN doc_estados ON(ges_unidad_documental . cod_esatdo = doc_estados . ide_estado) 
                    LEFT JOIN rad_terceros ON (ges_unidad_documental.ide_tercero_origen=rad_terceros.ide_tercero)
                    LEFT JOIN ges_tipos_documentales ON ges_tipos_documentales.`ide_tipo_documental`= ges_unidad_documental.ide_tipo_documental 
                    '
            . $tblsAdicional;

        $data = paginar_todos($tbls, $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        #unset($data['sql']);
        #echo $data['sql'];
        $datax = [];
        foreach ($data['rows'] as $reg) {
            $reg['dts_tercero'] = json_decode($reg['dts_tercero']);
            $reg['ots_dts_tercero'] = json_decode($reg['ots_dts_tercero']);
            $reg['func_responsables'] = explode(",", $reg['func_responsables']);
            $reg['is_file'] = !empty($reg['nom_archivo']) ? true : false;
            $datax[] = $reg;
        }
        $data['rows'] = $datax;
        $this->respuesta = $data;// json_decode(json_encode($data), true);
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function documento_get()
    {
        $this->auth();
        $codigo = (int)$this->uri->segment(4);

        $data = $this->bdGestion->getInfoDocumento($codigo);
//--
        if ((!isset($this->cnfUser->ventanilla) || empty($this->cnfUser->ventanilla)) && $this->certAut->ide_rol != 1) {
            $this->load->model('auditoria/traza_documento_model', 'traza');
            #documento revisado - Codigo 110
            #Se debe hacer consulta para saber si ya el usuario ha leido el docuemnto
            #{'cat_abierto':1,"fec_ult_lectura":date()}
            $dataTraza = [
                'ide_documento' => $codigo,
                'cod_radicado' => @$data['data']['cod_radicado'],
                'pk' => $codigo,
                'fecha_traza' => date('Y-m-d H:i:s'),
                'id_accion_traza' => 110,
                'json_usuario' => json_encode($this->certAut, JSON_NUMERIC_CHECK),
                'json_dts_accion' => json_encode(['cat_abierto' => 1, "fec_ult_lectura" => date('Y-m-d H:i:s')])
            ];
            $traza = $this->traza->set_data($dataTraza);
            $rta = $traza->insert();
            //var_dump($rta);
        }

        #var_dump($data);
        $data['data']['nom_tercero_origen'] = json_decode($data['data']['nom_tercero_origen']);
        $data['data']['ots_dts_tercero'] = json_decode($data['data']['ots_dts_tercero']);
        $data = json_decode(json_encode($data, JSON_NUMERIC_CHECK), true);
        $this->respuesta = $data;
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function verArchivo_get()
    {

        $codigo = (int)$this->uri->segment(4);
        $visor = (int)$this->uri->segment(5); // de acurdo al visor muestro el documento 1 de aplicacion 2 visor google
        $token = $this->uri->segment(6);

        $token = base64_decode($token);


        $this->auth($token);


        $data = $this->bdGestion->getNombreArchivo($codigo);
        $ruta = ROOTPATH . DIRECTORY_SEPARATOR . CLOUD_FILE . DIRECTORY_SEPARATOR . $data['nom_arc'];
        #print_r($data);
        $ruta = strtr(
            rtrim($ruta, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        );

        if (!file_exists($ruta)) {
            $ruta = '';
        }

        if ($visor == 2) {

            $this->load->helper('download');
            $dataFile = file_get_contents($ruta);
            force_download($data['nom_arc'], $dataFile);
            exit;
        } else {
            $this->output
                ->set_content_type('application/pdf')
                ->set_output(file_get_contents($ruta));
        }


    }

    public function printPlanillaDia_get($tipo, $cat)
    {
        $this->auth();
        if (empty($this->cnfUser->ventanilla) || $this->cnfUser->ventanilla <= 0) {
            $this->respuesta = [
                'error' => 401,
                'msg' => 'No se encuentra autorizado para ingresar a este módulo',
                'errores' => [],
            ];

            $this->error_rta = GD_Controller::HTTP_FORBIDDEN;
            $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
            exit(0);
        }

        $dataPaquetePrint = [
            'numero_hoja' => '',
            'origen' => 'GESTION',
            'cod_radicado_inicio' => '',
            'cod_radicado_final' => '',
            'ide_radicado_inicio' => null,
            'ide_radicado_final' => null,
            'ide_tipo_radicado' => (int)$cat,
            'fecha_impresion' => date('Y-m-d H:i:s'),
            'id_ventanilla' => $this->cnfUser->ventanilla
        ];


        $this->load->model('radicacion/paquete_impresion_model', 'tb_impresion');
        if ($tipo == 'list' || $tipo == 'ver') {
            $datos = $this->bdGestion->listPrintRadicados($cat);

            $datax = [];
            $nroReg = count($datos['data']);

            foreach ($datos['data'] as &$reg) {
                $reg['fun_dest'] = json_decode('[' . $reg['fun_dest'] . ']', true);
                $reg['dts_tercero'] = json_decode($reg['dts_tercero']);
                $reg['fun_dest'] = empty($reg['fun_dest']) ? [['nomapes' => 'No asignado']] : $reg['fun_dest'];
            }

            if ($nroReg > 0 && $tipo != 'ver') {
                $dataPaquetePrint['nro_resgistros'] = $nroReg;
                $dataPaquetePrint['origen'] = 'GESTION';
                $dataPaquetePrint['cod_radicado_inicio'] = $datos['data'][0]['nro_rad'];
                $dataPaquetePrint['cod_radicado_final'] = $datos['data'][$nroReg - 1]['nro_rad'];
                $dataPaquetePrint['ide_radicado_inicio'] = $datos['data'][0]['pk'];
                $dataPaquetePrint['ide_radicado_final'] = $datos['data'][$nroReg - 1]['pk'];
                $dataPaquetePrint['json_planilla'] = json_encode($datos['data'], JSON_NUMERIC_CHECK);

                $pqImpreso = $this->tb_impresion->set_data($dataPaquetePrint);
                $regImp = $pqImpreso->insert();
            }


            #$datos['data'] = $datax;
            $this->respuesta = $datos;

            $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
        } else {
            $l = null;
            $this->load->library('pdf');
            $pdf = new Pdf('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->titulo = "";
            $pdf->encabezado = $this->load->view("encabezados_reps/pdf_carta_h", null, true);
            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, 35, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
                require_once(dirname(__FILE__) . '/lang/eng.php');
                $pdf->setLanguageArray($l);
            }
            $datos = $this->bdGestion->listPrintRadicados($cat);

            $nroReg = count($datos['data']);
            if ($nroReg > 0 && $tipo != 'ver') {
                $dataPaquetePrint['nro_resgistros'] = $nroReg;
                $dataPaquetePrint['cod_radicado_inicio'] = $datos['data'][0]['nro_rad'];
                $dataPaquetePrint['cod_radicado_final'] = $datos['data'][$nroReg - 1]['nro_rad'];
                $dataPaquetePrint['ide_radicado_final'] = $datos['data'][$nroReg - 1]['pk'];
                $dataPaquetePrint['ide_radicado_inicio'] = $datos['data'][0]['pk'];
                $dataPaquetePrint['json_planilla'] = json_encode($datos['data'], JSON_NUMERIC_CHECK);


                $pqImpreso = $this->tb_impresion->set_data($dataPaquetePrint);
                $regImp = $pqImpreso->insert();
            }

            $html = $this->load->view('reportes_gestion/print_planilla_pdf', $datos, true);
            echo $html;

            $pdf->AddPage('L');
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->lastPage();

            $nombrePdf = 'Lista Radicados_' . date('Ymd_Hi') . '.pdf';

            $pdf->Output($nombrePdf, 'I');
        }
    }


    public function historial_get()
    {
        $this->auth();
        $codigo = (int)$this->uri->segment(4);


        $this->respuesta = $this->bdGestion->getTrazaDocumento($codigo);

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code


    }
}
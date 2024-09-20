<?php
class Radicar extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('radicacion/radicar_model', 'bdRadicar');
        $this->load->model('radicacion/anular_model', 'bdAnular');
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function paginar_post()
    {
        $this->auth();

        $this->load->helper('paginador_helper');
        $cmps = ['ide_radicado as pk', 'cod_radicado', 'fecha_documento_radicado as fec_doc_rad',
            'numero_documento_radicado as nro_doc',
            'asunto_documento as asunto', 'fecha_radicado', 'nom_tercero_origen as dts_tercero', 'nom_unidad_administrativa_origen as nom_off_origen',
            'nom_unidad_administrativa_destino as nom_off_destino', 'ide_tipo_radicado as tipo_rad', 'nom_funcionario_destino nom_fun_destino',
            'nom_funcionario_origen nom_fun_origen', 'nom_tipo_documental as tipo_doc',
            'fecha_documento_radicado as fec_doc', 'nro_anexos, numero_folios'
        ];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $filTc = $this->input->post('tiptc'); //categoria del radicado, 1=entrada, 2=salida 3 interno
        $filTd = $this->input->post('tiptd'); //Tipo docto radicado

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion = "activo_radicado = 'S'";

        #Si el el rol es de ventanilla y  tiene una ventanilla asignada
        if ($this->certAut->ide_rol == 3 && isset($this->cnfUser->ventanilla) && !empty($this->cnfUser->ventanilla)) {
            $ventanillaPublicaWeb = $this->cnfUser->ventanilla == 1 ? " or ide_ventanilla=99" : '';
            $condicion .= ' and ( ide_ventanilla=' . $this->cnfUser->ventanilla . $ventanillaPublicaWeb." ) ";
        } else {
            #Solo puede ver los asignados o enviados por el funcionario en el modulo de radicacion
            if (isset($this->cnfUser->filtro) && $this->cnfUser->filtro == 1) {
                $condicion .= ' AND (ide_funcionario_origen =' . $this->certAut->codFun . '';
                $condicion .= ' OR ide_funcionario_destino =' . $this->certAut->codFun . ') ';
            } elseif (isset($this->cnfUser->oficinas) && count($this->cnfUser->oficinas) > 0) {
                $condicion .= ' AND (ide_unidad_administrativa_destino IN(' . join($this->cnfUser->oficinas, ',') . ') ';
                $condicion .= ' OR ide_unidad_administrativa_origen IN(' . join($this->cnfUser->oficinas, ',') . ') ) ';
            }
        }

        if (!empty($filTc)) {
            $condicion .= ' and ide_tipo_radicado=' . (int)$filTc;
        }
        if (!empty($filTd)) {
            $condicion .= ' and ide_tipo_documental=' . (int)$filTd;
        }


        $data = paginar_todos('rad_radicados', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        #unset($data['sql']);
        #echo $data['sql'];
        $datax = [];
        foreach ($data['rows'] as $reg) {
            $reg['dts_tercero'] = json_decode($reg['dts_tercero']);
            $datax[] = $reg;
        }
        $data['rows'] = $datax;
        $this->respuesta = $data;// json_decode(json_encode($data), true);
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function saveEntrada_put()
    {
        $this->auth();

        if (empty($this->cnfUser->ventanilla) || $this->cnfUser->ventanilla <= 0) {
            $this->respuesta = [
                'error' => 901,
                'msg' => 'No esta autorizado para radicar documentos de ventanilla',
                'errores' => [],
            ];

            $this->error_rta = GD_Controller::HTTP_FORBIDDEN;
            $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
        }

        $data = $this->put();
        $this->load->library('form_validation');
        $data['ide_tipo_radicado'] = 1;
        $data['activo_radicado'] = 'S';
        $data['nom_usuario'] = $this->certAut->us;

        $data['fecha_registro'] = date('Y-m-d H:m:s');
        $data['ide_anexo_a'] = null;
        $data['ide_ventanilla'] = $this->cnfUser->ventanilla;

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('rad_entrada_put')) {

            $radicado = $this->bdRadicar->set_data($data);
            $this->respuesta = $radicado->insertEntrada();

            if ($this->respuesta['error']) {
                $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            }

        } else {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => $this->form_validation->error_array(),
                'msg' => 'Hay errores en la información del formulario',
                'errores' => [],
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function saveSalida_put()
    {
        $this->auth();

        if (empty($this->cnfUser->ventanilla) || $this->cnfUser->ventanilla <= 0) {
            $this->respuesta = [
                'error' => 901,
                'msg' => 'No esta autorizado para radicar documentos de ventanilla',
                'errores' => [],
            ];

            $this->error_rta = GD_Controller::HTTP_FORBIDDEN;
            $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
        }

        $data = $this->put();
        $this->load->library('form_validation');
        $data['ide_tipo_radicado'] = 2;
        $data['activo_radicado'] = 'S';
        $data['nom_usuario'] = $this->certAut->us;

        $data['fecha_registro'] = date('Y-m-d H:m:s');
        $data['ide_anexo_a'] = null;

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('rad_entrada_put')) {

            $radicado = $this->bdRadicar->set_data($data);
            $this->respuesta = $radicado->insertEntrada();

            if ($this->respuesta['error']) {
                $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            }

        } else {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => $this->form_validation->error_array(),
                'msg' => 'Hay errores en la información del formulario',
                'errores' => [],
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
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
            'origen' => 'RADICACION',
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
            $datos = $this->bdRadicar->listPrintRadicados($cat);

            $datax = [];
            $nroReg = count($datos['data']);
            #seteo los datos
            foreach ($datos['data'] as &$reg) {
                $reg['dts_tercero'] = json_decode($reg['dts_tercero']);
            }

            if ($nroReg > 0 && $tipo != 'ver') {
                $dataPaquetePrint['nro_resgistros'] = $nroReg;
                $dataPaquetePrint['cod_radicado_inicio'] = $datos['data'][0]['nro_rad'];
                $dataPaquetePrint['cod_radicado_final'] = $datos['data'][$nroReg - 1]['nro_rad'];
                $dataPaquetePrint['ide_radicado_inicio'] = $datos['data'][0]['pk'];
                $dataPaquetePrint['ide_radicado_final'] = $datos['data'][$nroReg - 1]['pk'];
                #Almaceno los datos de la planilla; para una posterior impresion.
                $dataPaquetePrint['json_planilla'] = json_encode($datos['data'], JSON_NUMERIC_CHECK);

                $pqImpreso = $this->tb_impresion->set_data($dataPaquetePrint);
                $regImp = $pqImpreso->insert();
            }

            #$datos['data'] = $datax;
            $this->respuesta = $datos;

            $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
        } else {
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
            $datos = $this->bdRadicar->listPrintRadicados($cat);

            $nroReg = count($datos['data']);
            if ($nroReg > 0 && $tipo != 'ver') {
                $dataPaquetePrint['nro_resgistros'] = $nroReg;
                $dataPaquetePrint['cod_radicado_inicio'] = $datos['data'][0]['nro_rad'];
                $dataPaquetePrint['cod_radicado_final'] = $datos['data'][$nroReg - 1]['nro_rad'];
                $dataPaquetePrint['ide_radicado_final'] = $datos['data'][$nroReg - 1]['pk'];
                $dataPaquetePrint['ide_radicado_inicio'] = $datos['data'][0]['pk'];


                $pqImpreso = $this->tb_impresion->set_data($dataPaquetePrint);
                $regImp = $pqImpreso->insert();
            }

            $html = $this->load->view('reportes_radicados/print_planilla_pdf', $datos, true);

            $pdf->AddPage('L');
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->lastPage();

            $nombrePdf = 'Lista Radicados_' . date('Ymd_Hi') . '.pdf';

            $pdf->Output($nombrePdf, 'I');
        }
    }

    #permite optener la iformacion del documento radicado.
    public function radicado_get()
    {
        $this->auth();

        $codigo = (int)$this->uri->segment(4);

        $data = $this->bdRadicar->getRadicado($codigo);
        #var_dump($data);
        $data['data']['nom_tercero_origen'] = json_decode($data['data']['nom_tercero_origen']);
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


        $data = $this->bdRadicar->getNombreArchivo($codigo);
        $ruta = ROOTPATH . DIRECTORY_SEPARATOR . CLOUD_FILE . DIRECTORY_SEPARATOR . $data['nom_arc'];

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

    public function anularRadicado_post(){
        $this->auth();

        if (empty($this->cnfUser->ventanilla) || $this->cnfUser->ventanilla <= 0) {
            $this->respuesta = [
                'error' => 901,
                'msg' => 'No esta autorizado para anular radicados ventanilla',
                'errores' => [],
            ];

            $this->error_rta = GD_Controller::HTTP_FORBIDDEN;
            $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
        }

        $data = $this->post();
        $this->load->library('form_validation');

        //print_r("datos que llegan de webgd ");
        //print_r($data);

        $data['fecha_anulado'] = date('Y-m-d H:m:s');
        $data['cod_user'] = $this->certAut->idu;

        $this->form_validation->set_data($data);
        if ($this->form_validation->run('rad_anulado_post')) {

            $radicado = $this->bdAnular->anularRadicado($data);
            $this->respuesta = $radicado;

            if ($this->respuesta['error']) {
                $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            }

        } else {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => $this->form_validation->error_array(),
                'msg' => 'Hay errores en la información del formulario',
                'errores' => [],
            ];
        }
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code

    }


}
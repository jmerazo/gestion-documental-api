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
 * User: JairDev
 * Date: 10/08/2018
 * Time: 5:02 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;


class Pqte_impresos extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('radicacion/paquete_impresion_model', 'tb_impresion');

        $this->error_rta = GD_Controller::HTTP_OK;
    }


    public function elimina_delete()
    {
        try {
            $codigo = $this->uri->segment(4);
            if (empty($codigo)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Se requiere mas información para eliminar el registro', $this->error_rta);
            }
            $cnd = 'cod_entidad =' . $this->db->escape($codigo);

            $rta = $this->tb_impresion->eliminar($codigo);
            $this->respuesta = [
                'msg' => 'Resgistro eliminado',
                'error' => false
            ];

        } catch (Exception $exc) {
            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => true,
                'error_num' => $exc->getCode(),
                'msg' => $exc->getMessage(),
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function paginar_post()
    {
        $this->load->helper('paginador_helper');
        $cmps = ['ide_impresion_radicado as pk', 'cod_radicado_inicio as rad_ini', 'cod_radicado_final as rad_fin',
            'ide_tipo_radicado', 'nro_resgistros as nro_reg', 'fecha_impresion as fec_print', 'ide_radicado_inicio as cd_ini',
            'ide_radicado_final as cd_fin, origen,json_planilla'];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $filTc = $this->input->post('tiptc'); //categoria del radicado, 1=entrada, 2=salida 3 interno
        $condicion = "estado = 1";

        if (isset($this->cnfUser->ventanilla) && !empty($this->cnfUser->ventanilla)) {
            $condicion .= ' and id_ventanilla= ' . $this->cnfUser->ventanilla;
        }

        if (!empty($filTc)) {
            $condicion .= ' and ide_tipo_radicado=' . (int)$filTc;
        }

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $data = paginar_todos('rad_impresion_radicados', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        unset($data['sql']);

        foreach ($data['rows'] as &$row) {
            $row['json_planilla'] = json_decode($row['json_planilla'], true);
        }


        $this->respuesta = $data;
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function printPlanilla_get($tipo, $codigo)
    {
        $l = null;
        $datos = $this->tb_impresion->getPlanilla($codigo);

        if ($tipo == 'print' || $tipo == 'ver') {
            $i = 0;
            /*
            foreach ($datos['data'] as &$reg) {
                $reg['fun_dest'] =  $reg['fun_dest'];
                $reg['dts_tercero'] = $reg['dts_tercero'];
                $reg['fun_dest'] = empty($reg['fun_dest']) ? [['nomapes' => 'No asignado']] : $reg['fun_dest'];
            }
*/
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

            $html = $this->load->view('reportes_radicados/print_planilla_pdf', $datos, true);

            $pdf->AddPage('L');
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->lastPage();

            $nombrePdf = 'Lista Radicados_' . date('Ymd_Hi') . '.pdf';

            $pdf->Output($nombrePdf, 'I');
        }
    }

    public function update_post()
    {
        $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;

        try {
            $codigo = $this->uri->segment(4);
            $dataPost = $this->input->post();

            $tiporad = $dataPost['tpRad'];
            $radIni = $dataPost['cdIni'];
            $radFin = $dataPost['cdFin'];
            $codVentanilla = $this->cnfUser->ventanilla;

            if (empty($codVentanilla)) {
                $this->error_rta = GD_Controller::HTTP_FORBIDDEN;
                throw new Exception('Ususario no autorizado para realizar la acción', $this->error_rta);
            }

            $datos = $this->tb_impresion->getListPlanilla($tiporad, $radIni, $radFin, $codVentanilla);

            foreach ($datos['data'] as &$reg) {
                $reg['fun_dest'] = json_decode('[' . $reg['fun_dest'] . ']', true);
                $reg['dts_tercero'] = json_decode($reg['dts_tercero']);
                $reg['fun_dest'] = empty($reg['fun_dest']) ? [['nomapes' => 'No asignado']] : $reg['fun_dest'];
            }

            $dataPaquetePrint['ide_impresion_radicado'] = $codigo;
            $dataPaquetePrint['json_planilla'] = json_encode($datos['data'], JSON_NUMERIC_CHECK);

            $pqImpreso = $this->tb_impresion->set_data($dataPaquetePrint);
            $regImp = $pqImpreso->update($dataPaquetePrint);


            $this->respuesta = $regImp;
            $this->error_rta = GD_Controller::HTTP_OK;

        } catch (Exception $exc) {
            $this->respuesta = [
                'error' => true,
                'error_num' => $exc->getCode(),
                'msg' => $exc->getMessage(),
            ];
        }

        $this->respuesta($this->respuesta, $this->error_rta);
    }
}


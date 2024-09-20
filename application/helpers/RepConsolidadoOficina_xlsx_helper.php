<?php

function repConsolidadoBySecretarias($filtro)
{
    $CI =& get_instance();

    $sql = "SELECT (
SELECT JSON_UNQUOTE(JSON_EXTRACT(raw_json, '$.nombre_dependencia')) FROM `ges_radicados_funcionarios` 
	WHERE ges_radicados_funcionarios.ide_unidad_documental = ges_unidad_documental.ide_unidad_documental 
	ORDER BY ide_radicados_leidos DESC LIMIT 1
) nom_off_destino, 
 COUNT(ges_unidad_documental.cod_radicado) AS total
FROM ges_unidad_documental 
    INNER JOIN doc_estados ON(ges_unidad_documental . cod_esatdo = doc_estados . ide_estado) 
    LEFT JOIN rad_terceros ON (ges_unidad_documental.ide_tercero_origen=rad_terceros.ide_tercero)
    LEFT JOIN ges_tipos_documentales ON ges_tipos_documentales.`ide_tipo_documental`= ges_unidad_documental.ide_tipo_documental 
WHERE 1=1  
	AND activo_radicado = 'S' 
	AND date(fecha_radicado) BETWEEN '${filtro['fecInicio']}'  AND '${filtro['fecFin']}' 
	AND  ges_tipos_documentales.respuesta ='S' 
	AND fecha_limite_respuesta < now()
GROUP BY nom_off_destino";

    $query = $CI->db->query($sql);
    $data = $query->result_array();

    $letExcel = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
        'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
        'CA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
        'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',
        'EA', 'EB', 'EC', 'ED', 'EE', 'EF', 'EG', 'EH', 'EI', 'EJ', 'EK', 'EL', 'EM', 'EN', 'EO', 'EP', 'EQ', 'ER', 'ES', 'ET', 'EU', 'EV', 'EW', 'EX', 'EY', 'EZ',
    );


    $CI->load->library('PHPExcel');


    $objPHPExcel = new PHPExcel();

// Set properties
    $objPHPExcel->getProperties()->setCreator("GESDOC - Prointel Putumayo")
        ->setLastModifiedBy("GESDOC")
        ->setTitle("Reporte consolidados radicados")
        ->setSubject("Sistema Gesdoc")
        ->setDescription("Consolidado de Documentos radicados por Secretaría")
        ->setCategory("Reportes GESDOC");

    $filenca = 2;

// Add some data
    $activeSheet = $objPHPExcel->setActiveSheetIndex(0);


    $activeSheet
        ->setCellValue('A' . ($filenca), "SECRETARÍA/OFICINA")
        ->setCellValue('B' . ($filenca), "TOTAL");


    $fil = $filenca + 1;
    $c = -1;
    foreach ($data as $reg) {
        $activeSheet
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["nom_off_destino"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["total"]);

        $activeSheet->getStyle('D' . $fil)->getAlignment()->setWrapText(true);
        $fil++;
        $c = -1;
    }

    $activeSheet->getColumnDimension('A')->setAutoSize(true);
    $activeSheet->getColumnDimension('B')->setAutoSize(true);

//ENCABEZADO
    $styleArray = [
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        ),
        'font' => array(
            'bold' => true
        )
    ];

    $rangoWrap = 'A' . ($filenca) . ":" . $letExcel[9] . ($filenca);
    $activeSheet->getStyle($rangoWrap)->applyFromArray($styleArray);

//CUADRICULA
    $styleArray = [
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_DASHED,
                'color' => ['argb' => '000'],
            ],
        ],
        'numberformat' => [
            'formatcode' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
        ]
    ];


    $rangoPrint = 'A' . $filenca . ':' . $letExcel[2] . ($fil + 1);
    $activeSheet->getStyle($rangoPrint)->applyFromArray($styleArray);

// Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Reporte radicados');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);

    $rangoPrint = 'A1:' . $letExcel[8] . ($fil);

    $activeSheet->getPageSetup()
        ->setPrintArea($rangoPrint)
        ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEGAL)
        ->setRowsToRepeatAtTopByStartAndEnd(1, 3)
        ->setHorizontalCentered(true)
        ->setVerticalCentered(false)
        ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
        ->setFitToWidth(1)
        ->setFitToHeight(0);

    $nomdoc ="REPORTE CONSOLIDADOS POR SECRETARIA " . date('Y-m-d H:i:s') . ".xlsx";


    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nomdoc . '"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}
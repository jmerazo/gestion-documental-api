<?php

function repListgral($data)
{
    $CI =& get_instance();

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
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Reporte radicados")
        ->setSubject("Sistema Gesdoc")
        ->setDescription("Lista d3e documentos radicados de acuerdo al filtro seleccionado")
        ->setCategory("Reportes GESDOC");

    $filenca = 2;

// Add some data
    $activeSheet = $objPHPExcel->setActiveSheetIndex(0);


    $activeSheet->setCellValue('A' . ($filenca), "NRO RADICADO")
        ->setCellValue('B' . ($filenca), "FECHA RADICADO")
        ->setCellValue('C' . ($filenca), "FECHA LIMITE RESPUESTA")
        ->setCellValue('D' . ($filenca), "FECHA RESPUESTA")
        ->setCellValue('E' . ($filenca), "MEDIO RECEPCIÓN")
        ->setCellValue('F' . ($filenca), 'TIPO DOCUMENTO')
        ->setCellValue('G' . ($filenca), 'ASUNTO')
        ->setCellValue('H' . ($filenca), 'NOMBRE ORIGEN')
        ->setCellValue('I' . ($filenca), 'ENTIDAD ORIGEN')
        ->setCellValue('J' . ($filenca), 'FUNCIONARIOS RESPONSABLES')
        ->setCellValue('K' . ($filenca), 'DEPENDENCIAS RESPONSABLES')
        ->setCellValue('L' . ($filenca), 'ESTADO');


    $fil = $filenca + 1;
    $c = -1;
    foreach ($data as $reg) {
        $activeSheet
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["cod_radicado"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["fec_doc_rad"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["fecha_limite_respuesta"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["fecha_respuesta"])
            ->setCellValueByColumnAndRow(++$c, $fil, "=SUMA($reg[fecha_limite_respuesta]-$reg[fecha_respuesta])")
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["medio_recepcion"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["tipo_doc"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["asunto"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["dts_tercero"]['nombre'])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["dts_tercero"]['entidad'])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["funcionarios_responsables"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["dependencias_responsables"])
            ->setCellValueByColumnAndRow(++$c, $fil, $reg["nombre_estado"]);

        $activeSheet->getStyle('D' . $fil)->getAlignment()->setWrapText(true);
        $fil++;
        $c = -1;
    }

    $activeSheet->getColumnDimension('A')->setAutoSize(true);
    $activeSheet->getColumnDimension('B')->setAutoSize(true);
    $activeSheet->getColumnDimension('C')->setAutoSize(true);
    $activeSheet->getColumnDimension('D')->setWidth(115);
    $activeSheet->getColumnDimension('E')->setAutoSize(true);
    $activeSheet->getColumnDimension('F')->setAutoSize(true);
    $activeSheet->getColumnDimension('G')->setAutoSize(true);
    $activeSheet->getColumnDimension('H')->setAutoSize(true);
    $activeSheet->getColumnDimension('I')->setAutoSize(true);
    $activeSheet->getColumnDimension('J')->setAutoSize(true);
    $activeSheet->getColumnDimension('K')->setAutoSize(true);
    $activeSheet->getColumnDimension('L')->setAutoSize(true);


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


    $rangoPrint = 'A' . $filenca . ':' . $letExcel[8] . ($fil + 1);
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

    $nomdoc = "REPORTE RADICADOS " . date('Y-m-d H:i:s') . ".xlsx";

// Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nomdoc . '"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;

}
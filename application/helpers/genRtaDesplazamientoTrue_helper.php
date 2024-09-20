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
 * Date: 30/05/20
 * Time: 11:32 AM
 */
function genDocumentoPermitida($nomFile, $data, $encabezado = '', $footer = '')
{

// Include the main TCPDF library (search for installation path).
//require_once('tcpdf_include.php');
    //require_once APPPATH . 'libraries/tcpdf/tcpdf.php';

// create new PDF document
    $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->titulo = '';
    $pdf->encabezado = $encabezado;
    $pdf->footerHtml = $footer;
// set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Gesdoc');
    $pdf->SetTitle($nomFile);
    $pdf->SetSubject('Respuesta solicitud movilidad');
    $pdf->SetKeywords('Gesdoc,Gobernacion, Prointel, Putumayo prointelsi');

// set default header data
    $pdf->SetHeaderData(ROOTPATH . '/images/pie_administracion.png', 600, 300 . ' 006', PDF_HEADER_STRING);

// set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // remove default header/footer
    // $pdf->setPrintHeader(false);
    // $pdf->setPrintFooter(false);

// set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
        require_once(dirname(__FILE__) . '/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

// ---------------------------------------------------------

// set font
    $pdf->SetFont('dejavusans', '', 10);

// add a page
    $pdf->AddPage();


    //$pdf->Image(APPPATH.'views/plantillagob2020.jpg', 10, 10, 75, 113, 'JPG', 'http://www.tcpdf.org', '', true, 150, '', false, false, 1, false, false, false);


    // $pdf->setSourceFile(APPPATH.'view/plantillagob2020.pdf');

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// create some HTML content
    $html = "<p>Sr. / Sra.<br>".$data['persona']['nombre'] .

        "<br>CC. ".$data['persona']['nit']." <br><br>Cordial saludo</p>";
    $html .= '<p style="text-align:justify">Con base en la información remitida por usted, se advierte que se encuentra dentro de los casos o actividades exceptuadas 
    mediante el Decreto Nacional No. 1076 del 28 de Julio del 2020 "Por el cual se imparten instrucciones en virtud de la emergencia sanitaria generada por la pandemia 
    del coronavirus COVID-19, y el mantenimiento del orden público". 
    </p>
    <p style="text-align:justify">Tal como lo establece el Artículo 3 del Decreto 1076 del 28 de Julio del 2020, los gobernadores y alcaldes en el marco de la emergencia 
sanitaria por causa del Coronavirus COVID-19, permitirán el derecho de circulación de las personas en los casos o actividades contenidas dentro de las excepciones del 
citado Decreto, y es obligatorio que las personas que desarrollen las actividades antes mencionadas deberán estar acreditadas o identificadas en el ejercicio de sus 
funciones o actividades, ante las autoridades que lo requieran para su movilización.</p>';

    $html .= 'Observaciones:<br>';
    $html .= '<ol style="text-align:justify">
    <li>Esta respuesta no es un permiso.</li>
    <li>Recuerde cumplir con las disposiciones de bioseguridad establecidas por el gobierno nacional para el ejercicio de las actividades.</li>
    <li>No olvide llevar toda la documentación necesaria con la cual pueda acreditar ante las autoridades competentes la excepción indicada por usted.</li>
    <li>Teniendo en cuenta que a la fecha no se encuentra habilitado el servicio de transporte público,  deberá realizarse en vehículo particular y si se trasporta más de una persona deberán utilizar tapabocas tiempo completo y mantener la distancia, no se permite la utilización de los vehículos de carga pesada y/o transporte de alimentos, ambulancias, servicios funerarios para su movilidad.</li>
    <li>En todo caso se debe cumplir con todas las medidas preventivas y de mitigación establecidas por el gobierno nacional, con el fin de reducir el riesgo de exposición y contagio de coronavirus COVID-19 (Resolución 666 del 24 de abril del 2020).</li>
    
    </ol>';


// output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->writeHTMLCell(50,8,140,40,$data['nrorad'],0);
    $style = array(
        'border' => 1,
        'vpadding' => 'auto',
        'hpadding' => 'auto',
        'fgcolor' => array(0,0,0),
        'bgcolor' => false, //array(255,255,255)
        'module_width' => 1, // width of a single module in points
        'module_height' => 1 // height of a single module in points
    );
    $pdf->write2DBarcode('Gob. Putumayo\nC.C. No:' . $data['persona']['nit']. '\nRad:' .$data['nrorad'], 'QRCODE,Q',  80, 200, 50, 50, $style, 'N');

// reset pointer to the last page
    $pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
    $pdf->Output($nomFile . '.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
}
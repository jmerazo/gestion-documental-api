<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';

class Pdf extends TCPDF
{
    public $periodo = '';
    public $titulo = '';
    public $enzabezado = '';
    public $footerHtml = '';
    public $CI;
    private $data = array();

    function __construct()
    {
        #$this->CI =& get_instance();
        parent::__construct();
        #$this->periodo=$this->CI->session->userdata('periodo_rep');
        #$this->titulo=$this->CI->session->userdata('titulo_rep');
    }

    //Page header
    public function Header()
    {
        // Logo
        #$image_file = $_SERVER["DOCUMENT_ROOT"].PRYPATH.'/assets/img/avatars/adam-jansen.jpg';
        #$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('times', 'B', 11);
        // Title
        #$this->Cell(0, 15, 'REPUBLICA DE COLOMBIA\nGOBERNACIÓN DEL PUTUMAYO\nNIT: 800094164-4', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        #$encabezado='<b>hola Munod <br>xxxx</b>';
        //$this->writeHTML($this->encabezado, true, false, true, false, 'C');
        $this->writeHTMLCell(0, 0, 10, 8, $this->encabezado, 0, 0, false, true, 'C', true);

    }

    // Page footer
    public function Footer()
    {

        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $this->AutoPageBreak;
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // write html image
//        $this->writeHTMLCell($w = 200, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 0, $fill = 0, $reseth = false, $align = 'C', $autopadding = false);
        $this->writeHTMLCell(210, 0, 0, 274, $this->footerHtml, 0, 0, false, true, 'C', true);

        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();


    }
}

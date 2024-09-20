<?php
/**
 * Created by PhpStorm.
 * User: dark_
 * Date: 29/03/2019
 * Time: 15:52
 */


require_once APPPATH . '/libraries/REST_Controller.php';
//require_once APPPATH . '/libraries/GD_Controller.php';
require_once APPPATH . '/libraries/JWT.php';

require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';

use \Firebase\JWT\JWT;

class Datos_pqrd extends GD_Controller
{
    private $result;
    private $error_rta;
    private $respuesta;

    function __construct()
    {
        parent::__construct();

        $this->load->database();

        $this->load->model('Terceros_model', 'bdTerceros');
        $this->error_rta = GD_Controller::HTTP_OK;

        $this->load->model('radicacion/radicar_model', 'bdRadicar');
        $this->error_rta = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->result = [
            'error' => true,
            'msg' => 'Falta información',
            'errores' => [],
        ];

        $this->load->model('pqrd/pqrd_model', 'bdPqrd');
        $this->error_rta = GD_Controller::HTTP_OK;

        $this->load->model('comun/Tipo_identificacion_model', 'tipo_identificacion');
        $this->error_rta = GD_Controller::HTTP_OK;

        $this->load->model('Tipo_documentos_model', 'mdTipDoc');
        $this->error_rta = GD_Controller::HTTP_OK;

        $this->load->model('Divipola_model', 'divipola');
        $this->error_rta = GD_Controller::HTTP_OK;

        $this->load->model('comun/dependencias_model', 'dependencias');
        $this->error_rta = GD_Controller::HTTP_OK;

    }

    //// ----------------------------------------------------------------
    ///  funcion para guardar datos pqrd relacionado a un tercero
    /// -----------------------------------------------------------------

    public function guardarPqrd_post()
    {
        $data = $this->post();
        $data = json_decode($data['request'], true);
        //echo "DATOS QUE LLEGAN DE ANGULAR PQRD";
        //var_dump($_FILES);

        $usuario = array(
            "ide_tipo_identf" => $data['tipoIdentificacion'],
            "nit_tercero" => $data['numeroIdentificacion'],
            "nombres_tercero" => $data['nombre'],
            "apellidos_tercero" => $data['apellido'],
            "cel_fijo_tercero" => $data['telefono'],
            "mail_tercero" => $data['email'],
            "direccion_tercero" => $data['direccion'],
            "ide_divipola_tercero" => $data['ubicacion']['id'],
            "tipo_tercero" => 0
        );

        $this->load->library('form_validation');

        $this->form_validation->set_data($usuario);

        if ($this->form_validation->run('terceros_put')) {

            $encontrado = $this->bdTerceros->getBuscarXDocumento($data['tipoIdentificacion'], $data['numeroIdentificacion']);

            if ($encontrado['data'] === "" || $encontrado['data'] === null) { // no lo encontro

                $nombres_tercero = $this->bdTerceros->set_data($usuario);
                $this->respuesta = $nombres_tercero->insert();

                if ($this->respuesta['error']) {

                    $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code

                } else {
                    $aux = $this->respuesta['cd_terceros'];
                }

            } else {
                $aux = $encontrado['data']['ide_tercero'];
            }


            if(isset($data['exception'])){
                $data['dependencias']=1;

                $this->load->model('Exception_model','mdexception');
                $exception=$this->mdexception->get($data['exception']);
                $data['asunto']=  $exception->nom;
                $data['descripcion']=$exception->nom;

                $data['observaciones_radicado']= $exception->consecutivo.' '. $exception->nom;

            }


            $pqrd = array(
                'ide_tercero_origen' => $aux,
                'ide_tipo_radicado' => 1,
                'activo_radicado' => 'S',
                'nom_usuario' => 'web.pqrd',
                'fecha_registro' => date('Y-m-d H:m:s'),
                'fecha_radicado' => date('Y-m-d H:m:s'),
                'fecha_documento_radicado' => date('Y-m-d H:m:s'),
                'ide_anexo_a' => null,
                'ide_ventanilla' => ID_VENTANILLA_WEB,
                'ide_medio_recepcion' => ID_MEDIO_RECEPCION,

                "ide_tipo_documental" => $data['tipoDoc'],
                "ide_unidad_administrativa_destino" => $data['dependencias'],
                "asunto_documento" => $data['asunto'],
                "observaciones_radicado" => $data['descripcion'],
                "numero_folios" => 1
            );

            // Validar si ide_tipo_documental es uno de los valores indicados
            $ide_tipo_documental = $pqrd['ide_tipo_documental'];
            $valid_ide_tipo_documental = in_array($ide_tipo_documental, [2, 3, 5, 6, 9, 36, 37]);

            // Calcular la fecha_limite_respuesta (15 días a partir de fecha_registro) si es válido
            if ($valid_ide_tipo_documental) {
                $fecha_registro = new DateTime($pqrd['fecha_registro']);
                $fecha_limite_respuesta = $fecha_registro->add(new DateInterval('P15D'))->format('Y-m-d H:m:s');
                $pqrd['fecha_limite_respuesta'] = $fecha_limite_respuesta;
            }

            if ($this->form_validation->run('rad_entrada_put')) {

                $radicado = $this->bdRadicar->set_data($pqrd);
                $rtaSave = $radicado->insertEntrada();
                $this->error_rta = REST_Controller::HTTP_OK;
                if (isset($_FILES['file'])) {
                    $rtaSave['file'] = $this->loadFile($rtaSave['nro_radicado'], $rtaSave['codigo']);
                }
                //$this->load->library('NodeSocket');
                //$this->socket->Emmit('notificapqrd', $rtaSave);

                $this->result = $rtaSave;
            } else {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                $this->result = [
                    'error' => true,
                    'msg' => 'Hay errores en la información del formulario con datos de pqrd',
                    'errores' => $this->form_validation->error_array(),
                ];
            }

            $this->response($this->result, $this->error_rta);

        } else {

            $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
            $this->respuesta = [
                'error' => true,
                'msg' => 'Hay errores en la información del formulario con datos del usuario',
                'errores' => $this->form_validation->error_array(),
            ];

            $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
        }

    }

    public function getDocumentRtaDesplazamiento_get()
    {

        $nroRadicado = $this->uri->segment(4);

        $this->load->model('radicacion/Radicar_model', 'mdradicar');
        $data = $this->mdradicar->getRadicadoByNroRad($nroRadicado);
        //var_dump($data);
        //exit;
        if (empty($data['data'])) {
            $this->load->view('errors/html/error_404', ['heading' => 'No encontrado', 'message' => 'Núero de radicado no encontrado']);
        }

        if ($data['data']['ide_tipo_radicado'] != 37) {
            $this->load->view('errors/html/error_404', ['heading' => 'No encontrado', 'message' => 'Documento activo para este proceso']);
        }

        $this->load->library('Pdf');
        $this->load->helper('genRtaDesplazamientoTrue');
        $dts = [
            'persona' => json_decode($data['data']['nom_tercero_origen'], true),
            'nrorad' => $nroRadicado,
        ];
        // var_dump($dts);
        //exit;

        $encabezado = $encabezado = $this->load->view('layouts/pdf/pdf_carta_v', array('titulo' => ''), true);
        $footer = $this->load->view('layouts/pdf/pie_administracion_digital', null, true);
        genDocumentoPermitida($nroRadicado, $dts, $encabezado, $footer);

    }


    //funcion para cargar archivo
    private function loadFile($nro_rad, $ide_rad)
    {
        try {

            $nom_arc = "P_" . $nro_rad;
            $archivos = $this->uploadAll($nom_arc);
            $this->respuesta = [
                'error' => false,
                'msg' => 'Archivo cargado correctamente',
                'arc' => $archivos];
            #var_dump($archivos);
            $this->load->database();
            $this->load->model('radicacion/radicar_model', 'radMd');


            $remplazar = ROOTPATH . DIRECTORY_SEPARATOR;

            $remplazar = strtr(
                rtrim($remplazar, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
            );

            $fullPath = $archivos['ruta'] . DIRECTORY_SEPARATOR . $archivos['files'][0]->name;


            $dts = ["nom_archivo" => $fullPath, "size_file" => $archivos['files'][0]->size];
            $r = $this->radMd->updateFile($ide_rad, $dts);
            #var_dump($r);
        } catch (Exception $e) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->respuesta = [
                'error' => $e->getCode(),
                'msg' => $e->getMessage(),
                'errores' => [],
            ];
        }
        return $this->respuesta;
    }

    private function uploadAll($nom_archivo)
    {
        ini_set('upload_max_size', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_execution_time', '300');


        $rutaArchivo = date('Y') . DIRECTORY_SEPARATOR . date('m')
            . DIRECTORY_SEPARATOR . date('d'); //estructura de archivo por año mes dia

        $c2 = ROOTPATH . DIRECTORY_SEPARATOR . CLOUD_FILE . DIRECTORY_SEPARATOR . $rutaArchivo;
        #echo $c2;

        if (!file_exists($c2)) {
            mkdir($c2, 0777, true);
        }


        #$nom_archivo = "A_" . $tp_doc . "_" . $nro_doc . "_" . substr(md5(date("Y-m-d H:i:s")), 4, 4);
        $config = array('upload_path' => $c2,
            'param_name' => 'file',
            'file_name' => $nom_archivo,
            'overwrite' => TRUE,
            'allowed_types' => 'txt|csv|pdf|doc|docx|xls|zip|rar|jpg|jpeg|png|tif|gif|xlsx',
            'max_size' => 1000 * 1024 * 1024 * 1024
        );

        $this->load->library('uploaddfull', $config);
        $datos = $this->uploaddfull->do_upload();
        $datos['ruta'] = $rutaArchivo;
        return $datos;
    }

    //// ----------------------------------------------------------------
    ///  funcion para buscar PQRD radicada por pin e id
    /// -----------------------------------------------------------------
    public function buscarPqrd_get()
    {

        $ideRad = $this->uri->segment(4);
        $pinPqrd = $this->uri->segment(5);

        $rta = $this->bdPqrd->getPqrd($ideRad, $pinPqrd);

        // con esto convierte lo que llega en json para acceder a los datos internos
        //$rta["data"]["dtsTercero"] = json_decode($rta["data"]["nom_tercero_origen"]);
        $rta["data"]["dtsTercero"] = json_decode($rta["data"]["datos_tercero_origen"]);

        // con esto elimina la variable que llega de bd
        //unset($rta["data"]["nom_tercero_origen"]);
        unset($rta["data"]["datos_tercero_origen"]);

        $this->respuesta($rta, $this->error_rta);

    }

    //// ----------------------------------------------------------------
    ///  funcion para buscar obtener los tipos de identificaciones
    /// -----------------------------------------------------------------

    public function combo_get()
    {
        $this->respuesta = $this->tipo_identificacion->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    //// ----------------------------------------------------------------
    ///  funcion para buscar obtener los tipos de documentos
    /// -----------------------------------------------------------------

    public function comboPqrd_get()
    {
        $this->respuesta = $this->mdTipDoc->getComboPqrd(true);
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    //// ----------------------------------------------------------------
    ///  funcion para buscar obtener la division politica de colombia
    /// -----------------------------------------------------------------

    public function comboDivipola_get()
    {
        $this->respuesta = $this->divipola->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        echo json_encode($this->respuesta);
        exit;
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    //// ----------------------------------------------------------------
    ///  funcion para buscar obtener las dependencias
    /// -----------------------------------------------------------------

    public function comboDependencias_get()
    {
        $this->respuesta = $this->dependencias->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    //// ----------------------------------------------------------------
    ///  Obtener excpciones de desplazamiento
    /// -----------------------------------------------------------------
    public function exceptions_get()
    {
        $this->load->model('Exception_model', 'exceptionmd');
        $this->respuesta = $this->exceptionmd->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    //// ----------------------------------------------------------------
    ///  funcion para buscar PQRD radicada por pin e id
    /// -----------------------------------------------------------------

    public function tiposDoc_get()
    {

        $rta = $this->bdPqrd->getTiposDocs();

        print_r($rta);

        $this->respuesta($rta, $this->error_rta);

    }

}// fin clase datos pqrd
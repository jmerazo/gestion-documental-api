<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 04/12/18
 * Time: 6:16 AM
 */

class Anexos extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        // Cargar la librería de email
        $this->load->library('email');
        $this->load->database();
        $this->load->model('Entornos/anexos_model', 'anexo');

        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function paginar_post()
    {
        $this->auth();

        $this->load->helper('paginador_helper');
        $cmps = ['ide_anexo', 'ide_unidad_dctal as ide_radicado', 'nro_documento', 'id_tipo_anexo', 'tipo_anexo', 'observaciones_tipo_anexo',
            'fecha_documento', 'estado_anexo', 'fecha_sys', 'ruta_file', 'observaciones', 'usuario', 'nom_usuario_carga'];

        $cdDocumento = $this->input->post('idrad');
        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $filTc = $this->input->post('tiptc'); //categoria del radicado, 1=entrada, 2=salida 3 interno
        $filTd = $this->input->post('tiptd'); //Tipo docto radicado

        $orden = $orden == 'true' ? 'DESC' : 'ASC';
        $condicion = ' estado_anexo="1" and ide_unidad_dctal="' . $cdDocumento . '" ';


        $data = paginar_todos('vwv_rad_anexos', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);
        #unset($data['sql']);
        #echo $data['sql'];
        $datax = [];

        #$data['rows'] = $data;
        $this->respuesta = $data;// json_decode(json_encode($data), true);
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function save_post()
    {
        $this->auth();

        try {
            $nro_rad = $this->post('nro_rd');
            $ide_rad = $this->post('pk');
            $formulario = $this->post('formulario');
            $frm = json_decode($formulario, true); // Decodificar el JSON del formulario
            $nom_arc = "P_" . $nro_rad;
            $seguimiento = $this->post('frmSeguimiento');
            $dtsSeguimiento = null;

            if (!empty($seguimiento) && !is_null($seguimiento)) {
                // Verifica si el seguimiento viene como objeto JSON y decodifica
                $frmSeg = json_decode($seguimiento, true);
                if (empty($frmSeg)) {
                    throw new Exception('Se requiere información del seguimiento', 9999);
                }

                // Asignar los valores del seguimiento decodificado a $dtsSeguimiento
                $dtsSeguimiento = [
                    'ide_unidad_doctal' => $ide_rad,
                    'text_gestion' => $frmSeg['text_gestion'] ?? 'No hay gestión',
                    'feccha_gestion' => date('Y-m-d H:i:s'),
                    'ide_usuario' => (int)$this->certAut->idu,
                    'nom_usuario' => $this->certAut->us_nom_apes,
                    'ide_anexo' => '',
                    'ide_estado' => $frmSeg['ide_estado'] ?? null, // Validar que 'ide_estado' esté presente
                    'mail_respuesta' => $frmSeg['mail_respuesta'] ?? null, // Extraer el correo para enviar
                    'cod_radicado' => $frmSeg['cod_radicado'] ?? 'N/A', // Radicado del documento
                    'fecha_radicado' => $frmSeg['fecha_radicado'] ?? 'N/A', // Fecha del radicado
                    'asunto_documento' => $frmSeg['asunto_documento'] ?? 'Sin asunto', // Asunto del documento
                    'nom_unidad_administrativa_destino' => $frmSeg['nom_unidad_administrativa_destino'] ?? 'Desconocida',
                    'raw_json' => null,
                ];
            }

            // Validación del formulario de archivo
            if (!isset($frm) || empty($frm)) {
                throw new Exception('Se requiere información del archivo para ser cargado', 9999);
            }

            $archivos = $this->uploadAll($nom_arc);
            if (!isset($archivos['files'])) {
                throw new Exception('Error al cargar el archivo', 9999);
            }

            $fullPath = $archivos['ruta'] . DIRECTORY_SEPARATOR . $archivos['files'][0]->name;

            $dts = [
                "nom_archivo" => $fullPath,
                "size_file" => $archivos['files'][0]->size,
                "ide_unidad_dctal" => $ide_rad,
                "id_tipo_anexo" => $frm['id_tipo_anexo'] ?? 'N/A', // Manejo de datos no definidos
                'observaciones' => $frm['observaciones'] ?? '',
                'fecha_documento' => $frm['fecha_documento'] ?? '',
                'pqrd' => $this->certAut->us,
                'nro_documento' => $frm['nro_documento'] ?? '',
                'nom_usuario_carga' => $this->certAut->us_nom_apes,
                'ruta_file' => $fullPath,
                'raw_json_file' => json_encode($archivos['files'][0]->infofile, JSON_NUMERIC_CHECK)
            ];

            // Proceso de inserción de archivo en la base de datos
            $this->anexo->set_data($dts);
            $r = $this->anexo->insert();

            if ($r['error'] == false) {
                $this->respuesta = [
                    'error' => false,
                    'msg' => 'Archivo cargado correctamente',
                    'arc' => $archivos,
                    'id_anexo' => $r['id_anexo'],
                ];

                // Si se ha cargado correctamente el archivo, procesamos el seguimiento
                if (!is_null($dtsSeguimiento)) {
                    $this->load->library('form_validation');

                    $dtsSeguimiento['ide_anexo'] = $r['id_anexo']; // Asignar el id_anexo al seguimiento
                    $dtsUser = (array)$this->certAut;
                    unset($dtsUser['config']);
                    $dtsFile = json_decode($dts['raw_json_file'], true);

                    // Combinar los datos de usuario y archivo en el campo raw_json
                    $dtsSeguimiento['raw_json'] = json_encode(array_merge((array)$dtsUser, (array)$dts), JSON_NUMERIC_CHECK);

                    // Validación del formulario de seguimiento
                    $this->form_validation->set_data($dtsSeguimiento);
                    if ($this->form_validation->run('seguimiento_put')) {
                        $this->load->model('gestion_documental/seguimiento_model', 'mdSeguimiento');
                        $model = $this->mdSeguimiento->set_data($dtsSeguimiento);
                        $result = $model->insert();

                        if ($result['error']) {
                            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
                        } else {
                            // Validar si el estado es 14 o 11 para enviar el correo
                            if ($dtsSeguimiento['ide_estado'] == 14 || $dtsSeguimiento['ide_estado'] == 11) {
                                // Si el estado es correcto, enviar el correo
                                $data = array_merge($dtsSeguimiento, ['documento_adjunto' => $fullPath]);
                                $this->send_mail($data);
                            }
                        }
                    } else {
                        $result = [
                            'error' => true,
                            'msg' => 'Hay errores en la información del formulario',
                            'errores' => $this->form_validation->error_array(),
                        ];
                    }
                }
            } else {
                $this->load->helper('file');
                delete_files($archivos['files'][0]->infofile['full_path']);
                $this->respuesta = [
                    'error' => true,
                    'msg' => 'Error en el procesamiento de datos para cargar el archivo,' . $r['error_msg'],
                    'arc' => $archivos
                ];
            }
            unset($archivos['files'][0]->infofile);

        } catch (Exception $e) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->respuesta = [
                'error' => $e->getCode(),
                'msg' => $e->getMessage(),
                'errores' => [],
            ];
        }
        $this->respuesta($this->respuesta, $this->error_rta);
    }

    private function send_mail($data)
    {
        // Asegurarse de cargar la librería de email si no ha sido cargada
        if (!$this->email) {
            $this->load->library('email');
        }

        // Configurar el remitente
        $this->email->from('no-reply@putumayo.gov.co', 'GesDoc | Gobernación del Putumayo');

        // Verificar si existe mail_respuesta
        if (isset($data['mail_respuesta'])) {
            $this->email->to($data['mail_respuesta']);
        } else {
            log_message('error', 'No se proporcionó mail_respuesta en $data');
            return;
        }

        // Configurar el destinatario
        $this->email->to($data['mail_respuesta']);

        // Asunto dinámico usando la variable cod_radicado
        $this->email->subject('GesDoc | Radicado: ' . $data['cod_radicado'] . ' - Nuevo seguimiento registrado');

        // Adjuntar las imágenes como contenido incrustado
        $logoEntidadPath = FCPATH . 'app/assets/img/logo_entidad_150x150.png'; // Ruta absoluta del servidor

        // Adjuntar las imágenes usando el método attach con el ID CID
        $this->email->attach($logoEntidadPath, 'inline', 'logo_entidad.png', '', true);

        // Obtener los CIDs de las imágenes adjuntas
        $logoEntidadCID = $this->email->attachment_cid($logoEntidadPath);

        // Construir el cuerpo del correo con HTML
        $mensaje = '
        <html>
        <head>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid black;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                }
                .header {
                    background-color: #5c9ae8;
                    color: white;
                }
                .logo-container {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .logo-container img {
                    margin: 0 15px;
                    height: 100px;
                    width: auto;
                }   
            </style>
        </head>
        <body>
            <div class="logo-container">
                <img src="cid:' . $logoEntidadCID . '" alt="Logo Gobernación del Putumayo" style="height: 100px;">
            </div>
            <div><h3>Nuevo Seguimiento Registrado</h3></div>

            <p>Estimado usuario,</p>
            <p>Se ha registrado un nuevo seguimiento sobre el documento radicado con la siguiente información:</p>

            <h4>Detalles del Radicado</h4>
            <table>
            <tr>
                <td class="header">Fecha radicado</td>
                <td>' . $data['fecha_radicado'] . '</td>
            </tr>
            <tr>
                <td class="header">Radicado #</td>
                <td>' . $data['cod_radicado'] . '</td>
            </tr>
            <tr>
                <td class="header">Asunto</td>
                <td>' . $data['asunto_documento'] . '</td>
            </tr>
        </table>

        <h4>Detalles de la Respuesta</h4>
        <table>
            <tr>
                <td class="header">Fecha respuesta</td>
                <td>' . $data['feccha_gestion'] . '</td>
            </tr>
            <tr>
                <td class="header">Unidad origen</td>
                <td>' . $data['nom_unidad_administrativa_destino'] . '</td>
            </tr>
            <tr>
                <td class="header">Respuesta</td>
                <td>' . nl2br($data['text_gestion']) . '</td>
            </tr>
        </table>

            <p>Documento Adjunto: P_' . (isset($data['cod_radicado']) ? $data['documento_adjunto'] : 'No hay documento adjunto') . '</p>

            <p style="color: red;">Por favor, no responda a este correo. Este correo se genera de forma automática por el sistema GesDoc.</p>
            
            <p>Atentamente,</p>
            <p>GesDoc | Gobernación del Putumayo</p>
        </body>
        </html>';

        // Asignar el cuerpo del mensaje al correo y definir que es HTML
        $this->email->message($mensaje);

        // Construir la ruta completa del archivo
        $archivo_ruta = FCPATH . 'archivos/anexos/' . $data['documento_adjunto'];

        // Verificar si el archivo existe antes de adjuntarlo
        if (file_exists($archivo_ruta)) {
            $this->email->attach($archivo_ruta);
        } else {
            log_message('error', 'No se encontró el archivo en: ' . $archivo_ruta);
        }

        // Enviar el correo
        if (!$this->email->send()) {
            log_message('error', 'Error al enviar correo: ' . $this->email->print_debugger());
        }
    }

    private function uploadAll($nom_archivo)
    {
        ini_set('upload_max_size', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_execution_time', '300');


        $rutaArchivo = date('Y') . DIRECTORY_SEPARATOR . date('m')
            . DIRECTORY_SEPARATOR . date('d'); //estructura de archivo por año mes dia

        $c2 = ROOTPATH . DIRECTORY_SEPARATOR . CLOUD_FILE . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . $rutaArchivo;
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

    public function download_get($id, $token)
    {

        $id = base64_decode($id);
        $token = base64_decode($token);

        $this->auth($token);

        $this->load->helper('download');
        $file = $this->anexo->getAnexo($id);


        $ruta = ROOTPATH . DIRECTORY_SEPARATOR . CLOUD_FILE . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . $file->ruta_file;
        if (!empty($file->ruta_file) && isset($ruta) && file_exists($ruta)) {
            $dts = json_decode($file->raw_json_file, true);
            $data = file_get_contents($ruta);
            $rname = $file->ruta_file;
            $name = isset($dts['client_name']) ? $dts['client_name'] : $rname;
            force_download($name, $data);
        } else {
            show_404();
        }
    }

    public function elimina_delete()
    {
        $this->auth();
        try {
            $codigo = $this->uri->segment(4);
            if (empty($codigo)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Se requiere mas información para eliminar el registro', $this->error_rta);
            }
            $rta = $this->anexo->eliminar($codigo);
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
}
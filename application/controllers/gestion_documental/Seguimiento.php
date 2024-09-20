<?php
/**
 * Copyright (c) 2019. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 14/09/2018
 * Time: 10:55 AM
 */
class Seguimiento extends GD_Controller
{
    private $result;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('gestion_documental/seguimiento_model', 'mdSeguimiento');
        $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function paginar_post($cod_rad)
    {
        $this->load->helper('paginador_helper');
        $cmps = ['ide_gestion as pk', 'ide_unidad_doctal as cod_rad', 'text_gestion',
            'feccha_gestion',
            'ide_usuario', 'nom_usuario', 'ide_anexo', 'raw_json'
        ];

        $nroPagina = $this->input->post('nro_pagina');
        $campoOrden = $this->input->post('cmp_orden');
        $orden = $this->input->post('orden');
        $parambus = $this->input->post('parambus');

        $orden = ($orden == 'true') ? 'DESC' : 'ASC';
        $condicion = ' ide_unidad_doctal="' . (int)$cod_rad . '" and reg_activo = "1"';


        $data = paginar_todos('ges_gestion', $cmps, null, $parambus, $condicion,
            $nroPagina, 15, $campoOrden, $orden);

        $datax = [];
        foreach ($data['rows'] as $reg) {
            $reg['raw_json'] = json_decode($reg['raw_json'], true);
            $reg['raw_json']['raw_json_file'] = @json_decode($reg['raw_json']['raw_json_file'], true);
            $datax[] = $reg;
        }
        $data['rows'] = $datax;

        $this->error_rta = GD_Controller::HTTP_OK;
        unset($data['sql']);
        #echo $data['sql'];
        $this->respuesta = $data;// json_decode(json_encode($data), true);
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function save_put()
    {
        try {
            $data = $this->put();
            $this->load->library('form_validation');
            $this->load->library('email');

            if (empty($data['pk'])) {
                throw new Exception('Se requiere informacion del seguimiento', 9999);
            }

            // Preparar datos adicionales
            $data['feccha_gestion'] = date('Y-m-d H:i:s');
            $data['raw_json'] = json_encode($this->certAut);
            $data['ide_usuario'] = (int)$this->certAut->idu;
            $data['nom_usuario'] = $this->certAut->us_nom_apes;
            $data['ide_unidad_doctal'] = $data['pk'];
            unset($data['pk']);

            // Validar datos del formulario
            $this->form_validation->set_data($data);
            if ($this->form_validation->run('seguimiento_put')) {
                $model = $this->mdSeguimiento->set_data($data);
                $this->result = $model->insert();

                if (!$this->result['error']) {
                    // Verificar si el estado es 14 o 11 para enviar el correo
                    if ($data['ide_estado'] == 14 || $data['ide_estado'] == 11) {
                        // Enviar el correo solo si el estado es "En espera de información" o "Documento Respondido"
                        $this->enviar_correo($data);
                    }

                    $this->error_rta = GD_Controller::HTTP_OK;
                    $this->result = [
                        'error' => false,
                        'msg' => 'Seguimiento guardado correctamente',
                    ];
                } else {
                    $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->result = [
                        'error' => true,
                        'msg' => 'Error al guardar el seguimiento',
                    ];
                }
            } else {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                $this->result = [
                    'error' => true,
                    'msg' => 'Hay errores en la información del formulario',
                    'errores' => $this->form_validation->error_array(),
                ];
            }
        } catch (Exception $e) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->result = [
                'error' => $e->getCode(),
                'msg' => $e->getMessage(),
                'errores' => [],
            ];
            log_message('error', 'Error en save_put: ' . $e->getMessage());
        }

        // Responder con los resultados
        $this->respuesta($this->result, $this->error_rta);
    }

    private function enviar_correo($data)
    {
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
        //$logoGesdocPath = FCPATH . 'app/assets/img/gesdoc_v2.png';

        // Adjuntar las imágenes usando el método attach con el ID CID
        $this->email->attach($logoEntidadPath, 'inline', 'logo_entidad.png', '', true);
        //$this->email->attach($logoGesdocPath, 'inline', 'logo_gesdoc.png', '', true);

        // Obtener los CIDs de las imágenes adjuntas
        $logoEntidadCID = $this->email->attachment_cid($logoEntidadPath);
        //$logoGesdocCID = $this->email->attachment_cid($logoGesdocPath);

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

            <p>Adjunto: ' . (isset($data['documento_adjunto']) ? $data['documento_adjunto'] : 'No hay documento adjunto') . '</p>

            <p style="color: red;">Por favor, no responda a este correo. Este correo se genera de forma automática por el sistema GesDoc.</p>
            
            <p>Atentamente,</p>
            <p>GesDoc | Gobernación del Putumayo</p>
        </body>
        </html>';

        // Asignar el cuerpo del mensaje al correo y definir que es HTML
        $this->email->message($mensaje);

        // Adjuntar el documento si existe
        if (isset($data['documento_adjunto'])) {
            $this->email->attach($data['documento_adjunto']);
        }

        // Enviar el correo
        if (!$this->email->send()) {
            log_message('error', 'Error al enviar correo: ' . $this->email->print_debugger());
        }
    }

}
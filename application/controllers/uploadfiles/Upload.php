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
 * Date: 19/09/2018
 * Time: 2:54 PM
 */
class Upload extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        $this->auth();
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function doc_radicado_post()
    {
        try {
            $nro_rad = $this->post('nro_rd');
            $ide_rad = $this->post('pk');

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
        } catch (Exception $e) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->respuesta = [
                'error' => $e->getCode(),
                'msg' => $e->getMessage(),
                'errores' => [],
            ];
        }
        $this->respuesta($this->respuesta, $this->error_rta); // OK (200) being the HTTP respuesta code
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

}
<?php
/**
 * Created by PhpStorm.
 * User: SysWork
 * Date: 10/09/2018
 * Time: 10:30 AM
 */

class Divipola extends GD_Controller
{
    private $respuesta;
    private $error_rta;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('Divipola_model', 'divipola');
        $this->error_rta = GD_Controller::HTTP_OK;
    }

    public function combo_get()
    {
        $this->respuesta = $this->divipola->getCombo();
        if ($this->respuesta['error']) {
            $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->respuesta($this->respuesta, $this->error_rta);
            exit;
        }
        echo json_encode($this->respuesta);
    }
}
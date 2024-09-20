<?php

/**
 * Created by Prointel Putumayo.
 * User: JairDev
 * Date: 29/01/19
 * Time: 4:13 PM
 */
class Asignacion extends GD_Controller
{
    private $result;
    private $error_rta;

    function __construct()
    {
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('gestion_documental/asignacion_model', 'mdAsigna');
        $this->error_rta = GD_Controller::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function list_asignados_get()
    {
        $codigo = $this->uri->segment(4);
        $this->result = $this->mdAsigna->getFuncAsignados($codigo);

        if (!$this->result ['error']) {
            $this->error_rta = GD_Controller::HTTP_OK;
            foreach ($this->result['data'] as &$reg) {
                $otraData = json_decode($reg['raw_json'], true);
                $otraDataUsuario = json_decode($reg['json_usuario'], true);
                unset($reg['raw_json']);
                $otraDataUsuario = ['canremove' => (isset($this->cnfUser->ventanilla) && !empty($this->cnfUser->ventanilla)) ? true : ((int)$otraDataUsuario['idu'] == (int)$this->certAut->idu)];
                unset($reg['json_usuario']);
                $reg = array_merge($reg, $otraData, $otraDataUsuario);
            }
        }
        $this->respuesta($this->result, $this->error_rta);
    }

    public function save_post()
    {
        $codigo = $this->uri->segment(4);
        $dtsFun = $this->post('dtsFun');
        $dtsFrm = json_decode($dtsFun, true);

        $data = [
            'ide_unidad_documental' => (int)$codigo,
            'ide_funcionario_lector' => (int)$dtsFrm['id'],
            'funcionario_responsable' => ((int)$dtsFrm['tipo'] == 1) ? 'S' : 'N',
            'ide_depen_funcionario' => (int)$dtsFrm['cod_dependencia'],
            'ide_cargo_fun' => (int)$dtsFrm['cod_cargo'],
        ];
        unset($dtsFrm['id'], $dtsFrm['tipo'], $dtsFrm['cod_dependencia'], $dtsFrm['cod_cargo']);
        $data['raw_json'] = json_encode($dtsFrm);
        $data['json_usuario'] = json_encode($this->certAut);

        $modelo = $this->mdAsigna->set_data($data);

        $this->result = $modelo->insert();

        if ($this->result['error'] == false) {
            $this->error_rta = GD_Controller::HTTP_OK;
        }

        $this->respuesta($this->result, $this->error_rta);
    }

    public function elimina_delete()
    {
        try {
            $codigo = $this->uri->segment(4);

            if (empty($codigo)) {
                $this->error_rta = GD_Controller::HTTP_BAD_REQUEST;
                throw new Exception('Se requiere mas inofrmacion para eliminar el registro', $this->error_rta);
            }
            $this->result = $this->mdAsigna->eliminar($codigo);

            if (!$this->result['error']) {
                $this->error_rta = GD_Controller::HTTP_OK;
            }

        } catch (Exception $exc) {
            $this->result = [
                'error' => true,
                'error_num' => $exc->getCode(),
                'msg' => $exc->getMessage(),
            ];
        }

        $this->respuesta($this->result, $this->error_rta); // OK (200) being the HTTP respuesta code
    }

    public function notificaMail_post()
    {
        try {
            $codigo = $this->uri->segment(4);

            $data = $this->mdAsigna->getMailFuncionarioXCedula('34571917');

            $listMails = $data['mails'] . join(',');
            $mail = $this->load->view("plantillas/" . $ptlMail, null, true);

            $this->_sendmail($listMails, $mail);
        } catch (Exception $ex) {

        }
    }

    private function _sendmail($mail = "", $ptlMailRad = "")
    {
        //cargamos la libreria email de ci
        //$this->load->library("email");
        try {
            $this->load->config('config_mails');
            $correoOrigen = "sistemasssd@putumayo.gov.co";

            $configGmail = array(
                'protocol' => $this->config->item('mail_protocol'),
                'smtp_host' => $this->config->item('smtp_host'),
                'smtp_port' => $this->config->item('smtp_port'),
                'smtp_user' => $this->config->item('smtp_user'),
                'smtp_pass' => $this->config->item('smtp_pass'),
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n"
            );

            $this->load->library("email", $configGmail);

            $this->email->set_newline("\r\n");

            $this->email->from($correoOrigen);
            $this->email->to($mail);
            //$this->email->cc($cc);
            $this->email->subject('Notificación de SIGDOC');
            $this->email->message($ptlMailRad);
            if ($this->email->send()) {
                #echo "mail enviado";
            } else {
                #echo $this->email->print_debugger();
            }
        } catch (Exception $ex) {

        }
    }

}
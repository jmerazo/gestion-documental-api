<?php
/**
 * Created by Prointel Putumayo.
 * User: Jair Muñoz
 * Date: 23/07/18
 * Time: 22:18
 */

defined('BASEPATH') OR exit('No direct script access allowed');


class Rad_correos extends GD_Controller
{
    private $params = array('connect_to' => '{correo.putumayo.gov.co:993/imap/ssl}',
        'user' => 'sistemasssd@putumayo.gov.co',
        'password' => 'Jarcho910*');

    public function __construct()
    {
        parent::__construct();
        #$h=apache_request_headers();
        #var_dump($h);
        $this->auth();
        #$this->load->library('mail_reader', $this->params, 'mailr');
        $this->load->library('imapreader', $this->params, 'mailr');
        #$h=apache_respuesta_headers();
        #var_dump($h);

    }

    public function index()
    {

    }

    public function list_mails_get()
    {
        try {
            #$this->mailr->connect($this->params);
            #$mails = $this->mailr->list_messages();

            $this->mailr->connect('{correo.putumayo.gov.co:993/imap/ssl}', 'sistemasssd@putumayo.gov.co', 'Jarcho910*');
            $mails = $this->mailr->getMessages('html');

        } catch (Exception $e) {
            $this->error = $e->getCode();
            $this->msg = $e->getMessage();
        }
        $this->respuesta($mails);
    }
}
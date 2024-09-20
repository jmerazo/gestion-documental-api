<?php
/**
 * Created by PhpStorm.
 * User: dark_
 * Date: 16/03/2019
 * Time: 11:29
 */

//http://api.gesdoc.local/pqrd/inicio

	class Inicio extends CI_Controller
    //class Inicio extends GD_Controller
    {
        private $result;
        private $error_rta;

        function __construct()
        {
            parent::__construct();

        }

        public function index()
        {
            $this->load->view('pqrd/inicio');
        }

        public function pqrd()
        {
            #cargas el view el formulario
            $this->load->view('pqrd/pqrd');
        }

        public function dash()
        {
            #cargas el view el formulario
            //echo "hola mundo";
        }

    }


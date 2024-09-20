<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        if ($_SERVER['HTTP_X_FORWARDED_SERVER'] == 'pqrd.putumayo.gov.co') {
            $this->load->view('pqrd/inicio');

        } else {
            redirect('http://www.putumayo.gov.co');
        }
    }
    public function hash(){
        $opciones = [
            'cost' => 10,
        ];
        $dts='$2y$10$k2ncscKeQcFr7hdyV5.30uJ91wkrJ4Gv8PpDTV4q.5MpbKeqjXPAS';
        $dts='$2y$10$ksuZ9w/tM49sICQjYSuJMOB76N231VhoXKHABkjz/i3vCSythlxnS';
        echo password_verify('1234',$dts);
        echo '<br>';
        echo password_hash("1234", PASSWORD_BCRYPT)."\n";
    }
}

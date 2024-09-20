<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UploadFull
 *
 * @author Jair Muñoz
 */
class Uploaddfull
{

    protected $options;

    function __construct($options = null)
    {

        $this->options = array(
            'upload_path' => 'files/',
            'mkdir_mode' => 0755,
            'param_name' => 'userfile',
            'allowed_types' => 'gif|jpg|png|zip|rar|doc|docx|xls|xlsx|pdf|tif|csv',
            'max_size' => '50000000',
            'encrypt_name' => false,
            'file_name' => null,
            'max_width' => '4000',
            'max_height' => '4000',
            'overwrite' => false,
        );
        if ($options) {
            $this->options = array_merge($this->options, $options);
            //var_dump(  $this->options);
        }
    }

    function do_upload()
    {
        $CI = &get_instance();
        $upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
        if (!is_dir(base_url() . $this->options['upload_path'])) {
            mkdir(base_url() . $this->options['upload_path'], $this->options['mkdir_mode'], true);
        }
        $config['upload_path'] = $this->options['upload_path'];
        $config['allowed_types'] = $this->options['allowed_types'];
        $config['max_size'] = $this->options['max_size'];
        $config['max_width'] = $this->options['max_width'];
        $config['max_height'] = $this->options['max_height'];
        $config['file_name'] = $this->options['file_name'];

        $CI->load->library('upload', $config);

        $num_archivos = @count($_FILES[$this->options['param_name']]['tmp_name']);
        $files = array();
        $info = new stdClass();
        if ($num_archivos > 1) {

            for ($i = 0; $i < $num_archivos; $i++) {

                $info->name = $_FILES[$this->options['param_name']]['name'][$i];
                $info->size = $_FILES[$this->options['param_name']]['size'][$i];
                $info->type = $_FILES[$this->options['param_name']]['type'][$i];
                $info->error = $_FILES[$this->options['param_name']]['error'][$i];

                $_FILES['userfile']['name'] = $info->name;
                $_FILES['userfile']['type'] = $info->type;
                $_FILES['userfile']['tmp_name'] = $_FILES[$this->options['param_name']]['tmp_name'][$i];
                $_FILES['userfile']['error'] = $info->error;
                $_FILES['userfile']['size'] = $info->size;

                if (!$CI->upload->do_upload()) {

                    $info->error = $CI->upload->display_errors("", "");
                    throw new Exception($info->error, 900);
                } else {
                    $dataArc = $CI->upload->data();
                    $info->url = $dataArc['full_path'];
                    $info->name = $dataArc['file_name'];
                    $info->size = $dataArc['file_size'];
                    $info->type = $dataArc['file_type'];
                    #$info->ext = "ext: ". $dataArc['file_ext'];
                    $info->infofile = $dataArc;
                    $info->error = 0;
                }

                $files[] = $info;
            }
        } else {

            $info->name = $_FILES[$this->options['param_name']]['name'];
            $info->size = $_FILES[$this->options['param_name']]['size'];
            $info->type = $_FILES[$this->options['param_name']]['type'];
            $info->error = $_FILES[$this->options['param_name']]['error'];
//            $info->otr = $_FILES[$this->options['param_name']];

            $_FILES['userfile']['name'] = $info->name;
            $_FILES['userfile']['type'] = $info->type;
            $_FILES['userfile']['tmp_name'] = $_FILES[$this->options['param_name']]['tmp_name'];
            $_FILES['userfile']['error'] = $info->error;
            $_FILES['userfile']['size'] = $info->size;

            if (!$CI->upload->do_upload()) {
//                var_dump($CI->upload->data());
                $info->error = $CI->upload->display_errors("", "");

                throw new Exception($info->error, 900);
            } else {

                $dataArc = $CI->upload->data();
                $info->url = base_url() . $this->options['upload_path'] . '/' . $dataArc['file_name'];
                $info->name = $dataArc['file_name'];
                $info->size = $dataArc['file_size'];
                $info->type = $dataArc['file_type'];
                $info->ext = $dataArc['file_ext'];
                $info->file_path = $dataArc['file_path'];
                $info->full_path = $dataArc['full_path'];
                $info->infofile = $dataArc;

                #$info->error = 0;
            }

            $files[] = $info;
        }
        return array('files' => $files);
    }

}

?>

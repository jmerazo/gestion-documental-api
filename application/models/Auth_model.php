<?php
/**
 * Created by PhpStorm.
 * User: JairMunoz
 * Date: 07/05/2018
 * Time: 3:40 PM
 */

if (!defined('BASEPATH'))
    exit('<h1>Error: 20001</h1><br> No se permite el acceso directo al script');

class Auth_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function auth($email, $password_input)
    {
        $this->db->select('cod_user as idu, concat_ws(" ",nombres, apellidos) as us_nom_apes, login AS us, 
            mail_user, estado_user, verified_email, ide_rol, ide_funcionario as codFun, 
            config_users as config, pass')
            ->where(array('mail_user' => $email, 'estado_user' => 1, 'verified_email' => 1)) // Asegúrate de que ambos estén en 1
            ->limit(1);
        
        $query = $this->db->get('seg_usuarios');
        $error = $this->db->error();
        
        if ((int)$error['code'] > 0) {
            throw new Exception($error['message'], $error['code']);
        }

        if ($query->num_rows() == 0) {
            return null; // Usuario no encontrado, inactivo o no verificado
        }

        $user = $query->row_array();
        $password_hash_db = $user['pass'];

        if (strlen($password_hash_db) == 32) {
            // Contraseña almacenada con md5
            if (md5($password_input) === $password_hash_db) {
                // Autenticación exitosa, rehashear contraseña con bcrypt
                $new_password_hash = password_hash($password_input, PASSWORD_BCRYPT);

                // Actualizar contraseña en la base de datos
                $this->db->where('cod_user', $user['idu']);
                $this->db->update('seg_usuarios', ['pass' => $new_password_hash]);

                // Eliminar 'pass' de los datos del usuario antes de devolverlos
                unset($user['pass']);
                return $user;
            } else {
                // Contraseña incorrecta
                return null;
            }
        } else {
            // Contraseña almacenada con password_hash
            if (password_verify($password_input, $password_hash_db)) {
                // Contraseña correcta
                unset($user['pass']);
                return $user;
            } else {
                // Contraseña incorrecta
                return null;
            }
        }
    }

    function menu($user_id, $rol)
    {
        //$user_perms_json = $this->cache->get("user_perms:$user_id");
        $user_perms_json = '';# $this->session->userdata("user_perms");
        //echo $user_perms_json;
        if (empty($user_perms_json)) {
            $sql = "CALL SP_GENERA_MENU(" . $this->db->escape($user_id) . ',' . $this->db->escape($rol) . ")";
            # echo $sql;
            # exit;
            $query = $this->db->query($sql);
            $rtaE = $this->db->error();
            if ($rtaE['code'] > 0) {
                #echo $rtaE['code'];
                return false;
            }
            $refs = array();
            // create and array to hold the list
            $list = array();
            if ($query->num_rows() > 0) {
                $row = $query->result_array();
                $userPerms = array();
                foreach ($row as $reg) {
                    if (strlen($reg['url_men']) > 3) {
                        $add = ($reg['add'] == '1') ? TRUE : FALSE;
                        $edt = ($reg['edit'] == '1') ? TRUE : FALSE;
                        $del = ($reg['del'] == '1') ? TRUE : FALSE;

                        $export = ($reg['exportar'] == '1') ? TRUE : FALSE;
                        $print = ($reg['print'] == '1') ? TRUE : FALSE;

                        $userPerms[$reg['klass']] = array('create' => $add, 'update' => $edt, 'delete' => $del, 'export' => $export, 'print' => $print);
                    }

                    // Assign by reference
                    $thisref = &$refs[$reg['ide_menu']];

                    // add the the menu parent
                    $thisref['ide_pad_men'] = $reg['ide_pad_men'];
                    $thisref['text'] = $reg['nom_menu'];
                    $thisref['sref'] = $reg['url_men'];
                    $thisref['img_menu'] = $reg['img_menu'];
                    #$thisref['ver'] = $reg['ver_menu'];
                    $thisref['rdn'] = $reg['orden'];
                    $thisref['tipo_menu'] = $reg['tipo_menu'];


                    // if there is no parent id
                    if ($reg['ide_pad_men'] == 0) {
                        $list[$reg['ide_menu']] = &$thisref;
                    } else {
                        $refs[$reg['ide_pad_men']]['submenu'][$reg['ide_menu']] = &$thisref;
                    }
                }
                $user_perms_json = $userPerms;
            } else {
                return FALSE;
            }
            //	$this->cache->save("user_perms:$user_id", $user_perms_json);
            #$this->session->set_userdata('user_perms', $user_perms_json);
            #$this->session->set_userdata('user_menu', $list);
        } else {
            $userPerms = json_decode($user_perms_json, TRUE);
        }

        return $list;
        #return arrayToObject($userPerms);
    }

    function load_permissions($user_id)
    {
        //$user_perms_json = $this->cache->get("user_perms:$user_id");
        $user_perms_json = '';# $this->session->userdata("user_perms");
        //echo $user_perms_json;
        if (empty($user_perms_json)) {
            $sql = "CALL SP_GENERA_MENU(" . $this->db->escape($user_id) . ")";
            $query = $this->db->query($sql);
            $rtaE = $this->db->error();
            if ($rtaE['code'] > 0) {
                return false;
            }
            $refs = array();
            // create and array to hold the list
            $list = array();
            if ($query->num_rows() > 0) {
                $row = $query->result_array();
                $userPerms = array();
                foreach ($row as $reg) {
                    if (strlen($reg['url_men']) > 3) {
                        $add = ($reg['add'] == '1') ? TRUE : FALSE;
                        $edt = ($reg['edit'] == '1') ? TRUE : FALSE;
                        $del = ($reg['del'] == '1') ? TRUE : FALSE;

                        $export = ($reg['exportar'] == '1') ? TRUE : FALSE;
                        $print = ($reg['print'] == '1') ? TRUE : FALSE;

                        $userPerms[$reg['klass']] = array('create' => $add, 'update' => $edt, 'delete' => $del, 'export' => $export, 'print' => $print);
                    }

                    // Assign by reference
                    $thisref = &$refs[$reg['ide_menu']];

                    // add the the menu parent
                    $thisref['ide_pad_men'] = $reg['ide_pad_men'];
                    $thisref['text'] = $reg['nom_menu'];
                    $thisref['sref'] = $reg['url_men'];
                    $thisref['img_menu'] = $reg['img_menu'];
                    #$thisref['ver'] = $reg['ver_menu'];
                    $thisref['rdn'] = $reg['orden'];

                    // if there is no parent id
                    if ($reg['ide_pad_men'] == 0) {
                        $list[$reg['ide_menu']] = &$thisref;
                    } else {
                        $refs[$reg['ide_pad_men']]['submenu'][$reg['ide_menu']] = &$thisref;
                    }
                }
                $user_perms_json = $userPerms;
            } else {
                return FALSE;
            }
            //	$this->cache->save("user_perms:$user_id", $user_perms_json);
            $this->session->set_userdata('user_perms', $user_perms_json);
            $this->session->set_userdata('user_menu', $list);
        } else {
            $userPerms = json_decode($user_perms_json, TRUE);
        }

        return $list;
        #return arrayToObject($userPerms);
    }
}
<?php
function encryptar($password) {
    $options = [
        'cost' => 12, // Aumenta el costo para mayor seguridad (pero mayor tiempo de procesamiento)
    ];
    return password_hash($password, PASSWORD_BCRYPT, $options);
}

function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];

    return $_SERVER['REMOTE_ADDR'];
}

function AudGetRealIP()
{
    $aud = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $aud = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $aud = $_SERVER['REMOTE_ADDR'];
    }
    $aud .= @$_SERVER['HTTP_USER_AGENT'];
    $aud .= gethostname();
    return sha1($aud);
}


function getAvatar($img) {
    $avatar = $_SERVER['DOCUMENT_ROOT'] . "/" . PRYPATH . '/assets/avatars/' . trim($img);

    if (!file_exists($avatar)) {
        $avatar = "avatar.png";
    } else {
        $avatar = $img;
    }
    return $avatar;
}
function generateRandomString($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ".uniqid()), 0, $length);
}
function ucword($ar) {
    $ar['nom'] = ucwords($ar['nom']);
    return $ar;
}

function ucword2($ar) {
    $ar['text'] = ucwords($ar['text']);
    return $ar;
}

function ucword3($ar) {
    $ar['nomapes_origen'] = ucwords($ar['nomapes_origen']);
    return $ar;
}
//Solo Etiqueta label
function ucword_label($ar) {
    $ar['label'] = ucwords($ar['label']);
    return $ar;
}

function parseString($string) {
    $string = str_replace("\b", "", $string);
    $string = str_replace("\t", " ", $string);
    $string = str_replace("\n", "<br>", $string);
    $string = str_replace("\f", "", $string);
    $string = str_replace("\r", "", $string);
    $string = str_replace("\u", "", $string);
    $sustituye = array("\r\n", "\n\r", "\n", "\r");
    $string = str_replace($sustituye, "", $string);
    return $string;
}

function parseString2($string) {
    $string = str_replace("\b", "", $string);
    $string = str_replace("\t", " ", $string);
    $string = str_replace("\n", "<br>", $string);
    $string = str_replace("\f", "", $string);
    $string = str_replace("\r", "", $string);
    $string = str_replace("\u", "", $string);

    return $string;
}

function getNavegador() {

    $browser = array("IE", "OPERA", "MOZILLA", "NETSCAPE", "FIREFOX", "SAFARI", "CHROME");

    $os = array("WIN", "MAC", "LINUX");
    # definimos unos valores por defecto para el navegador y el sistema operativo
    $info['browser'] = "OTHER";
    $info['os'] = "OTHER";
    # buscamos el navegador con su sistema operativo
    foreach ($browser as $parent) {
        $s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $parent);
        $f = $s + strlen($parent);
        $version = substr($_SERVER['HTTP_USER_AGENT'], $f, 15);
        $version = preg_replace('/[^0-9,.]/', '', $version);
        if ($s) {
            $info['browser'] = $parent;
            $info['version'] = $version;
        }
    }
    # obtenemos el sistema operativo

    foreach ($os as $val) {
        if (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $val) !== false)
            $info['os'] = $val;
    }
    # devolvemos el array de valores
    return $info;
}

/**
 *
 * @return bool Whether or not this is an AJAX request
 * @abstract Checks the current request header and determins whether or not this is an AJAX request
 */
function xhr_request() {
    if (strpos($_SERVER['HTTP_ACCEPT'], 'text/javascript') !== FALSE) {
        return TRUE;
    }
    return FALSE;
}

function response($success, $data = array(), $msg = '', $error = 0) {
    $CI = & get_instance();
    $data1["success"] = $success;
    $data1["error"] = $error;
    $data1["msg"] = $msg;
    $CI->output->set_content_type('application/json')
        ->set_output(json_encode(array_merge($data1, (array) $data)));
}

/**
 *
 * @return bool Whether or not this is an HTTP request
 * @abstract Checks the current request header and determins whether or not this is an HTML request
 */
function html_request() {
    if (strpos($_SERVER['HTTP_ACCEPT'], 'text/javascript') === FALSE && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== FALSE) {
        return TRUE;
    }
    return FALSE;
}

function arrayToObject($array) {
    if (!is_array($array)) {
        return $array;
    }
    $object = new stdClass();
    if (is_array($array) && count($array) > 0) {
        foreach ($array as $name => $value) {
            $name = strtolower(trim($name));
            if (!empty($name)) {
                $object->$name = arrayToObject($value);
            }
        }
        return $object;
    } else {
        return FALSE;
    }
}

function formatoPalabras($text, $form) {
    switch ($form) {
        case 0:
            return ucwords($text);
            break;
        case 1:
            return mb_strtoupper($text, 'UTF-8');
            break;
        case 2:
            return ucfirst($text);
            break;
        default:
            return ucwords($text);
    }
}

function Capitalizar($nombre) {
    // aca definimos un array de articulos (en minuscula)
    // aunque lo puedes definir afuera y declararlo global aca
    $articulos = array(
        '0' => 'a',
        '1' => 'de',
        '2' => 'del',
        '3' => 'la',
        '4' => 'los',
        '5' => 'las',
        '6' => 'y',
        '7' => 'o',
        '8' => 'u',
    );

    // explotamos el nombre
    $palabras = explode(' ', $nombre);

    // creamos la variable que contendra el nombre
    // formateado
    $nuevoNombre = '';

    // parseamos cada palabra
    foreach ($palabras as $elemento) {
        $elemento = mb_strtolower($elemento, 'UTF-8');
        // si la palabra es un articulo
        if (in_array(trim($elemento), $articulos)) {
            // concatenamos seguido de un espacio
            $nuevoNombre .= $elemento . " ";
        } else {
            // sino, es un nombre propio, por lo tanto aplicamos
            // las funciones y concatenamos seguido de un espacio
            $nuevoNombre .= mb_convert_case($elemento, MB_CASE_TITLE, "UTF-8") . " ";
        }
    }

    return trim($nuevoNombre);
}

function create_menu($arr, $op = 0) {
    $html = ($op == 0) ? "<ul class='nav nav-list' id='menuPrincipal'>" : "<ul class='submenu'>";
    foreach ($arr as $key => $v) {
        if (array_key_exists('children', $v)) {
            $html .= "<li class='hsub'>";
            $html .= '<a href="' . (empty($v['url_menu']) ? '#' : $v['url_menu']) . '"  class="dropdown-toggle" data-url="' . $v['url_menu'] . '"  >
                               <img class="icn" src="img/' . $v['img_menu'] . '"/>
                                <span class="menu-text">' . $v['nom_menu'] . '</span>
                                <b class="arrow fa fa-angle-down"></b>
                         </a> <b class="arrow"></b>';
            $html .= create_menu($v['children'], 1);
            $html .= "</li>";
        } else {
            $html .= '<li>
                        <a href="' . $v['url_menu'] . '" title="" class="menuItem" data-url="' . $v['url_menu'] . '" id="men_' . $v['url_menu'] . '">
                                <img class="icn" src="img/' . $v['img_menu'] . '"/>
                                <span class="menu-text">' . $v['nom_menu'] . '</span>
                         </a>
                      </li>';
        }
    }
    $html .= "</ul>";
    return $html;
}
function PermisosMenu($arr, $op = 0) {
    $html = ($op == 0) ? "<ol class='dd-list' id='listMenu'>" : "<ol class='dd-list' >";
    foreach ($arr as $key => $v) {
        if (array_key_exists('children', $v)) {
            $html .= "<li class='dd-item dd2-item'>";
            $html .= '
                <div class="dd-handle dd2-handle">
                <i class="fa '.$v['img_menu'].'"></i></div>
                <div class="dd2-content">'.$v['nom_menu'].'</div>
            ';
            $html .= PermisosMenu($v['children'], 1);
            $html .= "</li>";
        } else {
            $permisos=$v['permisos'];
            $html .= '<li class="dd-item dd2-item">
                        <div class="dd-handle dd2-handle"><i class="fa '.$v['img_menu'].'"></i></div>
                        <div class="dd2-content"><span>'.$v['nom_menu'].
                '</span><span uib-tooltip="Imprimir" class="checkbox pull-right no-margin">
                                <label>
                                    <input data-ng-click="cambiaEstado(\''.$v['klass'].'\',\'print\')" type="checkbox" '.(($permisos['print']==1) ? 'checked="checked"' : "") .' >
                                    <span class="text"></span>
                                </label>
                            </span>
                            <span uib-tooltip="Exportar" class="checkbox pull-right no-margin">
                                <label>
                                    <input data-ng-click="cambiaEstado(\''.$v['klass'].'\',\'export\')" type="checkbox" '.(($permisos['export']==1) ? 'checked="checked"' : "") .'>
                                    <span class="text"></span>
                                </label>
                            </span>
                            <span uib-tooltip="Eliminar" class="checkbox pull-right no-margin">
                                <label>
                                    <input data-ng-click="cambiaEstado(\''.$v['klass'].'\',\'delete\')" type="checkbox" '.(($permisos['delete']==1) ? 'checked="checked"' : "") .'>
                                    <span class="text"></span>
                                </label>
                            </span>
                            <span uib-tooltip="Actualizar" class="checkbox pull-right no-margin">
                                <label>
                                    <input data-ng-click="cambiaEstado(\''.$v['klass'].'\',\'update\')" type="checkbox" '.(($permisos['update']==1) ? 'checked="checked"' : "") .'>
                                    <span class="text"></span>
                                </label>
                            </span>
                            <span uib-tooltip="Adicionar" class="checkbox pull-right no-margin">
                                <label>
                                    <input data-ng-click="cambiaEstado(\''.$v['klass'].'\',\'create\')" type="checkbox" '.(($permisos['create']==1) ? 'checked="checked"' : "") .'>
                                    <span class="text"></span>
                                </label>
                            </span>
                        </div>

                      </li>';
        }
    }
    $html .= "</ol>";
    return $html;
}
function listOrdenada($cod = 0) {
    $CI = &get_instance();
    $CI->load->model("app_model");
    $rta = $CI->app_model->getDepsOrdenar($cod);
    $cadena = "";
    if (!empty($rta)) {
        $cadena = "<ol class='dd-list'>\n";
        foreach ($rta as $v) {
            $cadena.= "<li class='dd-item' data-id='{$v['ide_dependencia']}'>\n";
            $cadena.= "<div class='dd-handle'>{$v['ide_dependencia']}: {$v['nombre_dependencia']}</div>\n";
            $cadena.=listOrdenada($v['ide_dependencia']);
            $cadena.= "</li>\n";
        }
        $cadena.= "</ol>\n";
    }
    return $cadena;
}

function parseJsonArray($jsonArray, $parentID = 0, $cont = 0) {
    $return = array();
    $fil = 1;
    foreach ($jsonArray as $subArray) {
        $returnSubSubArray = array();
        if (isset($subArray['children'])) {
            $returnSubSubArray = parseJsonArray($subArray['children'], $subArray['id'], $fil);
            $fil++;
        }
        $return[] = array('id' => $subArray['id'], 'parentID' => $parentID, "fil" => $cont);
        $return = array_merge($return, $returnSubSubArray);
    }
    return $return;
}

function generaModal($nomModulo, $vista = '', $tit_form = "Formulario", $class_modal = 'modal-sm', $class_modal_body = '', $nom_btn_save = 'Guardar', $icon_form = "", $color_head_modal = COLOR_HEADER_MODAL) {
    $CI = &get_instance();

    $form['color_header'] = $color_head_modal;
    $form['titulo_form'] = $tit_form;
    $form['icon_form'] = $icon_form;
    $form['class_modal'] = $class_modal;
    $form['class_modal_body'] = $class_modal_body;
    if (!empty($vista)) {
        $form['modal_contenido'] = $CI->load->view($vista, array('modulo' => $nomModulo), TRUE);
    } else {
        $form['modal_contenido'] = '';
    }
    $form['modal_footer'] = $CI->load->view('plantillas/btn_pie_modal', array('modulo' => $nomModulo, 'nom_btn_save' => $nom_btn_save), TRUE);
    $contenido = $CI->load->view('plantillas/ptl_modal_contenido', $form, true);

    return $CI->load->view("layout/form_modal", array('modulo' => $nomModulo, 'contenido_form' => $contenido), true);
}

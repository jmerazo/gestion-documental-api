<?php
/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 8/08/2018
 * Time: 2:24 PM
 */

function generaWhere($cmpSearch = array(), $parambus = NULL, $cndAdicional = "")
{

    $where = 'where 1=1 ';
    $busca2 = false;
    $where .= (empty($cndAdicional)) ? '' : (' and ' . $cndAdicional);
    if ((!empty($parambus) && $parambus != 'false')) {

        if (isset($parambus['nomcmp'])) {
            if ($parambus['nomcmp'] != 'all') {
                $where .= ' and ' . $parambus['nomcmp'] . " like '%" . $parambus['valbus'] . "%'";
            } else {
                $cnd = "";
                foreach ($cmpSearch as $cmp) {
                    $cnd .= $cmp['id'] . " like '%" . $parambus['valbus'] . "%' or ";
                }
                $where .= ' and (' . substr($cnd, 0, strlen($cnd) - 4) . ")";
            }
        } else {
            $cnd = "";
            foreach ($cmpSearch as $cmp) {
                $cnd .= $cmp['id'] . " like '%" . $parambus . "%' or ";
            }
            $where .= ' and (' . substr($cnd, 0, strlen($cnd) - 4) . ")";
        }
        $busca2 = true;
    }

    return $where;
}

function paginar_todos($nom_tb, $cmpTblsVer, $cmpSearch = array(), $parambus = NULL, $cndAdicional = "",
                       $pagina = 1, $nro_reg_pagina = 20, $cmp_orden = 1, $dir_orden = '')
{

    if (!$cmp_orden)
        $cmp_orden = 1;

    if (!$dir_orden)
        $dir_orden = "";

    if (!$nro_reg_pagina)
        $nro_reg_pagina = 20;

    $start = $nro_reg_pagina * $pagina - $nro_reg_pagina;

    if ($start < 0)
        $start = 0;

    $rdr = '';
    if (!empty($cmp_orden)) {
        $dir_orden = empty($dir_orden) ? "ASC" : $dir_orden;
        $rdr = ' ORDER BY ' . $cmp_orden . " " . $dir_orden;
    }

    $where = generaWhere($cmpSearch, $parambus, $cndAdicional);

    $limit = 'LIMIT ' . $start . ',' . $nro_reg_pagina;

    $datos = lista_grilla($nom_tb, $cmpTblsVer, $where, $rdr, $limit, $pagina, $nro_reg_pagina);


    return array(
        'pag_actual' => (int)$pagina,
        'tot_paginas' => $datos['total'],
        'tot_registros' => $datos['records'],
        'reg_x_pag' => $nro_reg_pagina,
        'sql' => $datos['sql'],
        'rows' => $datos['rows'],
    );
}

function lista_grilla($tbl, $cmp, $whr, $rdr, $lmt, $page, $fin)
{
    $CI =& get_instance();
    $CI->load->database();

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(",", $cmp) . " FROM $tbl
            " . $whr . " " . $rdr . " " . $lmt;
    //echo $sql;
    //exit;
    $data["rt"] = TRUE;
    $data["sql"] = $sql;

    $query1 = $CI->db->query($sql);
    $infCnn = $CI->db->error();

    if ($infCnn['code'] > 0) {
        throw new Exception($infCnn['message'], $infCnn['code']);
        #return array("error" => $infCnn['code'], "rt" => FALSE, "sql" => $sql);
    }

    $data["total_reg"] = 0;
    $data["rows"] = array();
    if ($query1->num_rows() > 0) {

        $query2 = $CI->db->query("SELECT FOUND_ROWS() as cuenta ");

        $data["total_reg"] = $query2->row()->cuenta;
        $data["rows"] = $query1->result_array();
    }

    $count = isset($data['total_reg']) ? $data['total_reg'] : 0;

    if ($count > 0) {
        $total_pages = ceil($count / $fin);
    } else {
        $total_pages = 0;
    }

    if ($page > $total_pages) {
        $page = $total_pages;
    }

    return array(
        'page' => (int)$page,
        'total' => (int)$total_pages,
        'records' => (int)$count,
        'sql' => $data['sql'],
        'rows' => $data['rows'],
    );
}
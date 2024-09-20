<?php
/**
 * Copyright (c) 2019. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 11/04/19
 * Time: 10:31 PM
 */
class Dashboards_model extends CI_Model
{
    private $error_msg, $error_nro;

    public function __construct()
    {
        parent::__construct();
        $this->fecha_sys = date('Y-m-d H:i:s');
    }

    private function error()
    {
        $error = $this->db->error();
        $this->error_msg = $error['message'];
        $this->error_nro = $error['code'];
    }


    /**
     * Retorna el numero de docuemntos radicados por mes en un determindao año y dependiendo el tipo de documento
     * @param $anio
     * @param $tipoRadicado
     * @return array
     */
    public function radicadosXMes($anio = 2023, $tipoRadicado = 1)
    {

        $sql = 'SELECT CASE MONTH(fecha_radicado) WHEN 1 THEN "Enero"  WHEN 2 THEN "Febrero" WHEN 3 THEN "Marzo" WHEN 4 THEN "Abril" 
            WHEN 5 THEN "Mayo"  WHEN 6 THEN "Junio" WHEN 7 THEN "Julio" WHEN 8 THEN "Agosto" WHEN 9 THEN "Septiembre" 
            WHEN 10 THEN "Octubre" WHEN 11 THEN "Noviembre" WHEN 12 THEN "Diciembre" END nombre_mes, COUNT(ide_unidad_documental) as total 
            from ges_unidad_documental where DATE(fecha_radicado)>"' . FECHA_INIT_DATOS . '" AND ide_tipo_radicado=? and YEAR(fecha_radicado)=?
            ';

        $condicion = $this->wherePorUsuario();

        $sql .= $condicion;
        $sql .= ' GROUP BY MONTH(fecha_radicado), nombre_mes ORDER BY MONTH(fecha_radicado) ';

        $query = $this->db->query($sql, [$tipoRadicado, $anio]);

        $this->error();

        $respuesta = [
            'data' => $this->error_nro == 0 ? $query->result_array() : null,
            'error' => $this->error_nro > 0 ? true : false,
            'msg' => '',
            'error_msg' => $this->error_msg,
            'error_num' => $this->error_nro
        ];


        return $respuesta;
    }

    public function radicadosXTipoDocumento($anio = 2023, $tipoRadicado = 1, $wh = "")
    {

        $wh .= $this->wherePorUsuario();

        $sql = "select tipo_documental,   COUNT(ide_unidad_documental) AS total from ges_unidad_documental 
                    where DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "'  AND ide_tipo_radicado=$tipoRadicado and YEAR(fecha_radicado)=$anio "
            . $wh . "group by tipo_documental order by total desc limit 5";


        $query = $this->db->query($sql);

        $this->error();

        // echo $this->db->last_query();

        $respuesta = [
            'data' => $this->error_nro == 0 ? $query->result_array() : null,
            'error' => $this->error_nro > 0 ? true : false,
            'msg' => '',
            'error_msg' => $this->error_msg,
            'error_num' => $this->error_nro
        ];

        return $respuesta;
    }

    public function getMedioRecepcion($anio = 2023)
    {

        $wh = $this->wherePorUsuario();

        $sql = "SELECT medio_recepcion, COUNT(medio_recepcion) AS total 
        FROM ges_unidad_documental WHERE DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND YEAR(fecha_radicado) = $anio  $wh
        GROUP BY medio_recepcion ORDER BY total LIMIT 5";

        //$sql="SELECT ide_tipo_documental FROM prointel_ptelptyo.rad_radicados";

        $query = $this->db->query($sql);
        //echo $this->db->last_query();

        $respuesta = [
            'data' => $this->error_nro == 0 ? $query->result_array() : null,
            'error' => $this->error_nro > 0 ? true : false,
            'msg' => '',
            'error_msg' => $this->error_msg,
            'error_num' => $this->error_nro
        ];

        //print_r("lo que viene del modelo tipo documental");
        //print_r($respuesta['data']);

        return $respuesta;
    }

    public function getUnidadAdtiva($anio = 2022)
    {
        $sql = "SELECT  nombre_unidad_adtiva_destino, COUNT(nombre_unidad_adtiva_destino) cant 
FROM ges_unidad_documental WHERE DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND YEAR(fecha_radicado)=$anio 
GROUP BY YEAR(fecha_radicado), nombre_unidad_adtiva_destino   ORDER BY cant DESC LIMIT 10";

        //$sql="SELECT ide_tipo_documental FROM prointel_ptelptyo.rad_radicados";

        $query = $this->db->query($sql);
        #        echo $this->db->last_query();

        $respuesta = [
            'data' => $this->error_nro == 0 ? $query->result_array() : null,
            'error' => $this->error_nro > 0 ? true : false,
            'msg' => '',
            'error_msg' => $this->error_msg,
            'error_num' => $this->error_nro
        ];

        //print_r("lo que viene del modelo tipo documental");
        //print_r($respuesta['data']);

        return $respuesta;
    }

    public function getTotalesxDias()
    {
        $condicion = '';
        #Si el el rol es de ventanilla y  tiene una ventanilla asignada
        if ($this->certAut->ide_rol && isset($this->cnfUser->ventanilla) && !empty($this->cnfUser->ventanilla)) {
            $condicion .= ' and ( ide_ventanilla=' . $this->cnfUser->ventanilla . " or ide_ventanilla=99 ) ";
        }
        if ($this->cnfUser->filtro && $this->cnfUser->filtro == 1) {
            #la condicion es si esta como funcionario lector
            $condicion .= ' AND (FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';
            $condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';
        } elseif (isset($this->cnfUser->oficinas) && count($this->cnfUser->oficinas) > 0) {
            $condicion .= ' and ((FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';
            $condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';

            //$condicion .= ' or (ide_unidad_adtiva_destino IN(' . join($this->cnfUser->oficinas, ',') . ') ';
            //$condicion .= ' OR ide_unidad_adtiva_origen IN(' . join($this->cnfUser->oficinas, ',') . '))) ';
            $condicion .= ')';
        }

        $sql = "SELECT COUNT(*) total,
                        CASE
            WHEN DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) <1 THEN 
            JSON_OBJECT('nom','Vencidos - no contestados a tiempo','color','bg-danger','orden',1,'codFiltro','vencidos')
            WHEN DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) <=2 THEN 
            JSON_OBJECT('nom','Entre 1 y 2 días para dar respuesta','color','bg-pink','orden',2,'codFiltro','1-2')
            WHEN DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) <=5 THEN 
            JSON_OBJECT('nom','Entre 3 y 5 días para dar respuesta','color','bg-warning','orden',3,'codFiltro','3-5')
            WHEN DATEDIFF(ges_unidad_documental.fecha_limite_respuesta, DATE(NOW())) <=10 THEN 
            JSON_OBJECT('nom','Entre 6 y 10 días para dar respuesta','color','bg-green','orden',4,'codFiltro','6-10')
            ELSE  JSON_OBJECT('nom','Más de 10 días para responder','color','bg-success','orden',5,'codFiltro','M10')
            END             AS `tipo`
            FROM ges_unidad_documental 
            WHERE activo_radicado = 'S' and NOT ISNULL(ges_unidad_documental.fecha_limite_respuesta) 
            AND DATE(ges_unidad_documental.fecha_radicado)> '" . FECHA_INIT_DATOS . "' AND cod_esatdo<>11  
            $condicion
            GROUP BY `tipo` 
            ORDER BY tipo->'$.orden' asc;";
//echo $sql;
        $query = $this->db->query($sql);
        $this->error();
        $dataEst = $this->error_nro == 0 ? $query->result_array() : [];

        //$dataEst = array_merge($obj, $dataEst);

        $devueltos = [];

        if ($this->certAut->ide_rol && isset($this->cnfUser->ventanilla) && !empty($this->cnfUser->ventanilla)) {
            $query2 = $this->db->query("SELECT COUNT(*) AS total, JSON_OBJECT('nom','Devueltos a ventanilla','color','bg-purple','orden',0,'codFiltro','rad-devueltos')  AS tipo FROM ges_unidad_documental WHERE cod_esatdo =10");
            $devueltos = $this->error_nro == 0 ? $query2->result_array() : [];
        }

        //echo $this->db->last_query();

        $data = array_merge($devueltos, $dataEst);


        $respuesta = [
            'data' => $data,
            'error' => $this->error_nro > 0 ? true : false,
            'msg' => '',
            'sql' => $sql,
            'error_msg' => $this->error_msg,
            'error_num' => $this->error_nro
        ];

        //print_r("lo que viene del modelo tipo documental");
        //print_r($respuesta['data']);

        return $respuesta;
    }

    public function getBassPrin()
    {
        $wh = $this->wherePorUsuario();

        $sql1 = "SELECT COUNT(ide_unidad_documental) as t_hoy  FROM ges_unidad_documental WHERE DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND DATE(fecha_radicado)=DATE(NOW()) $wh ";
        $sql2 = "SELECT COUNT(ide_unidad_documental) as t_ayer FROM ges_unidad_documental WHERE DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND DATE(fecha_radicado)=DATE(NOW() - INTERVAL 1 DAY ) $wh ";
        $sql3 = "SELECT COUNT(ide_unidad_documental) as t_sem FROM ges_unidad_documental WHERE  DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND WEEKOFYEAR(fecha_radicado)=WEEKOFYEAR(NOW())  AND YEAR(fecha_radicado)=YEAR(NOW()) $wh ";
        $sql4 = "SELECT COUNT(ide_unidad_documental) as t_sem_ant FROM ges_unidad_documental WHERE DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND WEEKOFYEAR(fecha_radicado)=WEEKOFYEAR(NOW() - INTERVAL 1 WEEK)  AND YEAR(fecha_radicado)=YEAR(NOW()) $wh ";
        $sql5 = "SELECT COUNT(ide_unidad_documental) as t_mes FROM ges_unidad_documental WHERE DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND MONTH(fecha_radicado)=MONTH(NOW())  AND YEAR(fecha_radicado)=YEAR(NOW()) $wh ";
        $sql6 = "SELECT COUNT(ide_unidad_documental) as t_mes_ant FROM ges_unidad_documental WHERE DATE(fecha_radicado)>'" . FECHA_INIT_DATOS . "' AND MONTH(fecha_radicado)=MONTH(NOW()- INTERVAL 1 MONTH) AND YEAR(fecha_radicado)=YEAR(NOW()) $wh ";

        //echo $sql6;

        $sql = "SELECT ($sql1) as t_hoy, ($sql2) as t_ayer, ($sql3) as t_sem,  ($sql4) as t_sem_ant, ($sql5) as t_mes, ($sql6) as t_mes_ant from DUAL";


        $query = $this->db->query($sql);
        //echo $this->db->last_query();

        $respuesta = [
            'data' => $this->error_nro == 0 ? $query->row() : [],
            'error' => $this->error_nro > 0 ? true : false,
            'msg' => '',
            'sql' => $this->db->last_query(),
            'error_msg' => $this->error_msg,
            'error_num' => $this->error_nro
        ];

        //print_r("lo que viene del modelo tipo documental");
        //print_r($respuesta['data']);

        return $respuesta;
    }


    private function wherePorUsuario()
    {
        $condicion = '';
        #Si el el rol es de ventanilla y  tiene una ventanilla asignada
        if ($this->certAut->ide_rol && isset($this->cnfUser->ventanilla) && !empty($this->cnfUser->ventanilla)) {
            $condicion .= ' and ( ide_ventanilla=' . $this->cnfUser->ventanilla . " or ide_ventanilla=99 ) ";
        }


        if ($this->cnfUser->filtro && $this->cnfUser->filtro !== 3) {

            #Solo puede ver los asignados o enviados por el funcionario en el modulo de radicacion
            #crear trigger para cuando se agregue un radicado cree una asignacion principal
            #crear trigguer para cuando se asigne(elimine) un documento a un funcionario actualice un campo en la unidad documenta, donde se almacene todos los codigos de funcionarios q pueden ver el documento

            if ($this->cnfUser->filtro && $this->cnfUser->filtro == 1) { //solo los asignados
                #la condicion es si esta como funcionario lector
                $condicion .= ' AND (FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';
                $condicion .= ' OR FIND_IN_SET("' . $this->certAut->codFun . '", func_lectores))';
            } elseif (isset($this->cnfUser->oficinas) && count($this->cnfUser->oficinas) > 0) {

                $condicion .= ' and (FIND_IN_SET("' . $this->certAut->codFun . '", func_responsables) ';

                //traigo todos los funcionarios q forman parte del area para luego armar la condicion
                $codFuns = $this->bdGestion->getIdFuncionariosDependencia($this->cnfUser->oficinas[0]);

                foreach ($codFuns['data'] as $r) {
                    $condicion .= ' OR FIND_IN_SET("' . $r . '", func_responsables) ';
                }


                //                $condicion .= " OR FIND_IN_SET('" . $this->certAut->codFun . "', func_lectores))";

                $condicion .= ' ) ';
            }
        }
        return $condicion;
    }
}
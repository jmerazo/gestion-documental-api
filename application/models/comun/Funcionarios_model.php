<?php
/**
 * Copyright (c) 2018. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: JairDev
 * Date: 27/08/2018
 * Time: 11:32 AM
 */
class Funcionarios_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function getCombo()
    {
        $query = $this->db->select('ide_funcionario as id,nom_apes as nom')
            ->where('activo', 'S')
            ->get('vwv_comun_funcionarios');
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

    public function getFuncActivos()
    {
        if ($this->certAut->ide_rol == 3) { // si el usuario es de ventanilla
            $where = '';
            if (isset($this->cnfUser->oficinas) && count($this->cnfUser->oficinas) > 0) {
                $where .= ' and cod_dependencias="' . $this->cnfUser->oficinas[0] . '"';
            }

            $sql = 'SELECT adm_personas.ide_funcionario AS id,
                    adm_personas.nro_identificacion 
                    , LOWER(CONCAT_WS(" ",adm_personas.nombre1_funcionario
                    , adm_personas.nombre2_funcionario
                    , adm_personas.apellido1_funcionario
                    , adm_personas.apellido2_funcionario)) AS nomapes    
                    , adm_personas.genero_funcionario
                    , adm_personas.celular_funcionario
                    , adm_personas.mail_funcionario
                    , adm_personas.foto_funcionario
                    , aux_cargos.cod_cargo
                    , aux_cargos.nom_cargo
                    , cod_dependencia
                    , adm_dependencias.nombre_dependencia
                FROM
                    adm_personas 
                    INNER JOIN aux_cargos 
                        ON (adm_personas.cod_cargo = aux_cargos.cod_cargo)
                    INNER JOIN adm_dependencias 
                        ON(adm_dependencias.cod_dependencias=adm_personas.`cod_dependencia`)
                WHERE (1=1 and adm_personas.activo ="S" ' . $where . ' )';
        } else {

            $sql1 = 'SELECT cod_dependencia FROM adm_personas WHERE ide_funcionario = ' . $this->certAut->codFun;

            $codDependencia = $this->db
                ->where('ide_funcionario', $this->certAut->codFun)
                ->get('adm_personas')
                ->row()->cod_dependencia;
            //echo $this->db->last_query();

            $sql = "SELECT DISTINCT adm_personas.ide_funcionario AS id,
                    adm_personas.nro_identificacion 
                    , LOWER(CONCAT_WS(' ',adm_personas.nombre1_funcionario
                    , adm_personas.nombre2_funcionario
                    , adm_personas.apellido1_funcionario
                    , adm_personas.apellido2_funcionario)) AS nomapes    
                    , adm_personas.genero_funcionario
                    , adm_personas.celular_funcionario
                    , adm_personas.mail_funcionario
                    , adm_personas.foto_funcionario
                    , adm_personas.cod_dependencia
                    , t.nombre_dependencia
                    , aux_cargos.cod_cargo
                    , aux_cargos.nom_cargo 
                    FROM adm_personas
                      INNER JOIN aux_cargos 
                        ON (adm_personas.cod_cargo = aux_cargos.cod_cargo)
                     JOIN (WITH RECURSIVE dependencias_recursiva 
                        AS (
                              SELECT `cod_dependencias`, `nombre_dependencia` FROM `adm_dependencias` WHERE padre=$codDependencia  OR `cod_dependencias`=$codDependencia 
                              UNION ALL
                              SELECT d.`cod_dependencias`, d.nombre_dependencia FROM `adm_dependencias` d 
                             JOIN dependencias_recursiva ON dependencias_recursiva.cod_dependencias = d.padre
                            )
                        SELECT cod_dependencias, nombre_dependencia
                            FROM dependencias_recursiva) t ON t.cod_dependencias = adm_personas.`cod_dependencia`
                            WHERE  adm_personas.`activo`='S'	
                            ORDER BY nombre_dependencia DESC;";

        }
		//echo $sql;


        $query = $this->db->query($sql);
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => '',
        ];
        return $respuesta;
    }


    //optine las oficinas q estan bajo mi organigrama
    public function miOrganigrama()
    {
        if ($this->certAut->ide_rol == 3) {
            $codDependencia = 1;
        } else {
            $codDependencia = $this->db
                ->where('ide_funcionario', $this->certAut->codFun)
                ->get('adm_personas')
                ->row()->cod_dependencia;
        }


        $sql = "WITH RECURSIVE  dependencias_recursiva AS (
      SELECT cod_dependencias, nombre_dependencia, padre FROM adm_dependencias 
	WHERE padre=$codDependencia OR cod_dependencias=$codDependencia
      UNION ALL
      SELECT d.cod_dependencias, d.nombre_dependencia, d.padre FROM adm_dependencias d 
     JOIN dependencias_recursiva ON dependencias_recursiva.cod_dependencias = d.padre)
        SELECT DISTINCT cod_dependencias AS id, nombre_dependencia AS nom, padre
            FROM dependencias_recursiva";

			
        $query = $this->db->query($sql);
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => '',
        ];
        return $respuesta;
    }
}
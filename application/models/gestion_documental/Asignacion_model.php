<?php

/**
 * Created by Prointel Putumayo.
 * User: JairDev
 * Date: 29/01/19
 * Time: 4:13 PM
 */
class Asignacion_model extends CI_Model
{

    protected $table = 'ges_radicados_funcionarios';

    public $ide_radicados_leidos;
    public $ide_unidad_documental;
    public $ide_funcionario_lector;
    public $funcionario_responsable;
    public $ide_secre_funcionario;
    public $ide_depen_funcionario;
    public $ide_cargo_fun;
    public $fecha_asignacion;
    public $fecha_lectura;
    public $ide_funcionario_asigna;
    public $raw_json;
    public $json_usuario;

    public function __construct()
    {
        parent::__construct();
        $this->fecha_asignacion = date('Y-m-d H:i:s');
        $this->ide_funcionario_asigna = $this->certAut->idu;
    }

    public function set_data($data_form)
    {
        foreach ($data_form as $campo => $valor) {
            if (property_exists("Asignacion_model", $campo)) {
                $this->$campo = $valor;
            }
        }
        return $this;
    }

    private function error()
    {
        $error = $this->db->error();
        $this->error_msg = $error['message'];
        $this->error_nro = $error['code'];
    }

    public function insert()
    {
        $rta = $this->db->insert($this->table, $this);
        if ($rta) {
            $cd = $this->db->insert_id();
            $respuesta = [
                "error" => false,
                'msg' => "Registro guardado correctamente",
                'codigo' => $cd,
            ];
        } else {
            $error = $this->error();
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $error['message'],
                'error_num' => $error['code']
            ];
        }
        return $respuesta;
    }

    public function getFuncAsignados($codDocumento)
    {
        $sql = 'SELECT ges_radicados_funcionarios.`ide_radicados_leidos` AS pk,
    ges_radicados_funcionarios.`ide_funcionario_lector` AS id
    , ges_radicados_funcionarios.ide_depen_funcionario
    , (CASE funcionario_responsable WHEN "S" THEN 1 WHEN "N" THEN 0 END)    AS tipo
    , ges_radicados_funcionarios.`raw_json`,ges_radicados_funcionarios.json_usuario
FROM
    ges_radicados_funcionarios
	where ide_unidad_documental=' . $codDocumento;

        $query = $this->db->query($sql);
        $respuesta = [
            'data' => $query->result_array(),
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }

    public function eliminar($codigo)
    {
        $this->db->where('ide_radicados_leidos', $codigo);
        $rta = $this->db->delete($this->table);

        if ($rta) {
            $respuesta = [
                "error" => false,
                'msg' => "Registro eliminado correctamente",
                'cod_asignacion' => $codigo
            ];
        } else {
            $this->db->error();
            $respuesta = [
                "error" => true,
                'msg' => "Error al guardar nuevo registro",
                'error_msg' => $this->error_msg,
                'error_num' => $this->error_nro
            ];
        }
        return $respuesta;
    }

    /**
     * @param $nroDocumento Nro de documento de la persona o funcionario
     * @return array
     */
    public function getMailFuncionarioXCedula($nroDocumento)
    {
        $respuesta = [
            'data' => null,
            'error' => true,
            'msg' => 'No se entrontraron datos'
        ];
        $data = [];
        $sql = 'SELECT mail_funcionario, nom_apes FROM  vwv_comun_funcionarios WHERE trim(nro_identificacion) ="' . trim($nroDocumento) . '";';
        $query = $this->db->query($sql);

        $data[] = $query->row(0)->mail_funcionario;

        $respuesta = [
            'mails' => $data,
            'error' => false,
            'msg' => ''
        ];
        return $respuesta;
    }
}
<?php
class Principal extends GD_Controller
{
    private $resulEstadistica;

    public function __construct()
    {
        parent::__construct();
        $this->auth();
        $this->load->database();
        $this->load->model('Estadisticas/Dashboards_model', 'md_dash');
        $this->load->model('gestion_documental/gestion_model', 'bdGestion');
        $this->initializeStatistics();
    }

    private function initializeStatistics()
    {
        $this->resulEstadistica = [
            'rad-devueltos' => [
                'tipo' => ['nom' => 'Devueltos a ventanilla', 'color' => 'bg-govco-purple', 'orden' => 0, 'codFiltro' => 'rad-devueltos'],
                'total' => 0
            ],
            'vencidos' => [
                'tipo' => ['nom' => 'Vencidos - no contestados a tiempo', 'color' => 'bg-govco-red', 'orden' => 1, 'codFiltro' => 'vencidos'],
                'total' => 0
            ],
            '1-2' => [
                'tipo' => ['nom' => 'Entre 1 y 2 días para dar respuesta', 'color' => 'bg-govco-orange', 'orden' => 2, 'codFiltro' => '1-2'],
                'total' => 0
            ],
            '3-5' => [
                'tipo' => ['nom' => 'Entre 3 y 5 días para dar respuesta', 'color' => 'bg-govco-yellow', 'orden' => 3, 'codFiltro' => '3-5'],
                'total' => 0
            ],
            '6-10' => [
                'tipo' => ['nom' => 'Entre 6 y 10 días para dar respuesta', 'color' => 'bg-govco-green', 'orden' => 4, 'codFiltro' => '6-10'],
                'total' => 0
            ]
        ];
    }

    public function radicadosxMes_get()
    {
        $anio = (int)$this->uri->segment(4);

        if ($anio <= 0) {
            return $this->sendResponse([], 'Año inválido.', GD_Controller::HTTP_BAD_REQUEST);
        }

        $rta = $this->md_dash->radicadosXMes($anio);

        log_message('debug', 'Respuesta del modelo radicadosXMes: ' . print_r($rta, true));

        if ($rta['error']) {
            return $this->sendResponse([], 'Error al obtener los datos.', GD_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $result = [
            'labels' => array_column($rta['data'], 'nombre_mes'),
            'datos' => array_column($rta['data'], 'total')
        ];

        log_message('debug', 'Respuesta del modelo radicadosXMes result: ' . print_r($result, true));

        return $this->sendResponse($result, 'Datos obtenidos exitosamente.');
    }

    public function radicadosxTipoDocumento_get()
    {
        $anio = (int)$this->uri->segment(4);

        if ($anio <= 0) {
            return $this->sendResponse([], 'Año inválido.', GD_Controller::HTTP_BAD_REQUEST);
        }

        $condicion = '';
        $rta = $this->md_dash->radicadosXTipoDocumento($anio, 1, $condicion);

        if ($rta['error']) {
            return $this->sendResponse([], 'Error al obtener los datos.', GD_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $result = [
            'labels' => array_column($rta['data'], 'tipo_documental'),
            'datos' => array_column($rta['data'], 'total')
        ];

        return $this->sendResponse($result, 'Datos obtenidos exitosamente.');
    }

    public function radicadosxMedioRecepcion_get()
    {
        $anio = (int)$this->uri->segment(4);

        if ($anio <= 0) {
            return $this->sendResponse([], 'Año inválido.', GD_Controller::HTTP_BAD_REQUEST);
        }

        $rta = $this->md_dash->getMedioRecepcion($anio);

        if ($rta['error']) {
            return $this->sendResponse([], 'Error al obtener los datos.', GD_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $result = [
            'labels' => array_column($rta['data'], 'medio_recepcion'),
            'datos' => array_column($rta['data'], 'total')
        ];

        return $this->sendResponse($result, 'Datos obtenidos exitosamente.');
    }

    public function radicadosxUnidadAdtiva_get()
    {
        $anio = (int)$this->uri->segment(4);

        if ($anio <= 0) {
            return $this->sendResponse([], 'Año inválido.', GD_Controller::HTTP_BAD_REQUEST);
        }

        $rta = $this->md_dash->getUnidadAdtiva($anio);

        if ($rta['error']) {
            return $this->sendResponse([], 'Error al obtener los datos.', GD_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $result = [
            'labels' => array_column($rta['data'], 'nombre_unidad_adtiva_destino'),
            'datos' => array_column($rta['data'], 'cant')
        ];

        return $this->sendResponse($result, 'Datos obtenidos exitosamente.');
    }

    public function regsPorTipo_get()
    {
        $rta = $this->md_dash->getTotalesxDias();

        if ($rta['error']) {
            return $this->sendResponse([], 'Error al obtener los datos.', GD_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $rs = array_map(array('Principal', 'castCmp'), $rta['data']);
        foreach ($rs as $item) {
            $this->resulEstadistica[$item['tipo']['codFiltro']]['total'] = $item['total'];
        }

        return $this->sendResponse(array_values($this->resulEstadistica), 'Datos obtenidos exitosamente.');
    }

    public function regsBassPrin_get()
    {
        $rta = $this->md_dash->getBassPrin();

        return $this->sendResponse($rta, 'Datos obtenidos exitosamente.');
    }

    private function castCmp($a)
    {
        $a['tipo'] = json_decode($a['tipo'], true);
        return $a;
    }
}
<div class="container-fluid">
    <div class="row">

        <div class="col-sm-3"><img src="app/assets/img/logo_entidad_150x150.png" alt="Logo putumayo"></div>
        <div class="col-sm-5 text-center">
            <b>REPUBLICA DE COLOMBIA</b><br>
            <b>GOBERNACIÓN DEL PUTUMAYO</b><br>
            "TRECE MUNICIPIOS UN SOLO CORAZON"<br>
            <i>¡Gracias Dios mio por tantas bendiciones!</i>

        </div>
        <div class="col-sm-4"><img src="app/assets/img/logo_periodo_350x150.png" alt="Logo periodo Gobernación"></div>
    </div>

</div>
<div class="row">

    <div class="col-sm-12">
        <h1 align="center"> {{ definiciones.definiciones[0].definicion }} </h1> <!-- titulo pqrd -->
    </div>

</div>

<div class="container-fluid" ng-hide="bandera"> <!-- si desea -->
    <h5 align="left">
        <i class="fas fa-check" id="iCheck"></i>
        {{ definiciones.definiciones[7].definicion }}
        <strong> " {{ definiciones.definiciones[7].resaltar }} ". </strong>
    </h5>
    <h5 align="left">
        <i class="fas fa-check" id="iCheck2"></i>
        {{ definiciones.definiciones[7].definicion2 }}
        <strong> " {{ definiciones.definiciones[7].resaltar2 }} ". </strong>
    </h5>
</div>

<div class="container-fluid" ng-hide="bandera"> <!-- opciones -->
    <div class="row">
        <br>
        <?php $this->load->view('pqrd/pqrdTemplates/pqrdRadicar') ?>
        <?php $this->load->view('pqrd/pqrdTemplates/pqrdBuscar') ?>
        <br>
    </div>
    <br>
</div>

<div class="container-fluid" ng-show="bandera"> <!-- formularios -->
    <br>
    <div ng-show="opcRadicar"> <?php $this->load->view('pqrd/pqrdTemplates/pqrdFormRadicar') ?> </div>
    <div ng-show="opcBuscar"> <?php $this->load->view('pqrd/pqrdTemplates/pqrdFormBuscar') ?> </div>
    <br>
</div>


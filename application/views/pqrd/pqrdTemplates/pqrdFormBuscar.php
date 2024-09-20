<!--  <div ng-include="pqrdBotones"></div>  BOTONES GUARDAR Y CANCELAR -->
<?php $this->load->view('pqrd/pqrdTemplates/pqrdBotones') ?> <!-- BOTONES GUARDAR Y CANCELAR -->

<br>

<div class="container-fluid">

    <div class="card">

        <div class="card-header" id="cardBuscarHeader">
            <i class="fas fa-grip-horizontal"></i>
            <strong>INFORMACIÓN DE LA PQRD</strong>
        </div>

        <div class="card-body" id="cardBuscarBody">


            <div class="row"> <!-- FILA DATOS PQRD -->

                <div class="container-fluid">

                    <div class="col col-md-6">

                        <p class="text-primary"><strong>Código radicado:</strong></p>
                        <p class="text-danger"> {{ pqrdEcontrada.cod_radicado }}</p>
                        <br>
                        <p class="text-primary"><strong>Fecha de radicado:</strong></p>
                        <p class="text-danger"> {{ pqrdEcontrada.fecha_radicado }}</p>

                    </div>

                    <div class="col col-md-6">

                        <p class="text-primary"><strong>Tipo de PQRD:</strong></p>
                        <p class="text-danger"> {{ pqrdEcontrada.tipo_documental }}</p>
                        <br>
                        <p class="text-primary"><strong>Fecha límite respuesta</strong>:</p>
                        <p class="text-danger"> {{ pqrdEcontrada.fecha_limite_respuesta }} </p>

                    </div>

                </div>

            </div> <!-- FIN FILA DATOS PQRD -->

            <div class="row"> <!-- FILA DATOS REMITENTE Y DESTINATARIO -->


                    <div class="col col-md-6">

                        <div id="divBuscar1" align="center">
                            <strong>REMITENTE</strong>
                        </div>
                        <br>
                        <div class="col col-md-6">
                            <p class="text-info"><strong>Nit:</strong></p>
                            <p> {{ pqrdEcontrada.dtsTercero.nit }}</p>
                        </div>
                        <div class="col col-md-6" ng-show="remiteUsu">
                            <p class="text-info"><strong>Nombre:</strong></p>
                            <p> {{ pqrdEcontrada.dtsTercero.nombre }}</p>
                        </div>
                        <br>
                        <br>
                        <div class="col col-md-12" ng-hide="remiteUsu">
                            <p class="text-info"><strong>Entidad</strong>:</p>
                            <p> {{ pqrdEcontrada.dtsTercero.entidad }} </p>
                        </div>
                        <br>

                    </div>

                    <div class="col col-md-6">

                        <div id="divBuscar2" align="center">
                            <strong>DESTINATARIO</strong>
                        </div>
                        <br>
                        <!-- <div class="col col-md-12">
                            <p class="text-success"><strong>Entidad</strong>:</p>
                            <p> {{ entidad }}</p>
                        </div>
                        <br>  -->
                        <div class="col col-md-6">
                            <p class="text-success"><strong>Depedencia:</strong></p>
                            <p> {{ pqrdEcontrada.nombre_unidad_adtiva_destino }} </p>
                        </div>
                        <div class="col col-md-6">
                            <p class="text-success"><strong>Responsable:</strong></p>
                            <p> {{ pqrdEcontrada.nombre_funcionario_destino }} </p>
                        </div>
                        <br>

                    </div>

            </div> <!-- FIN FILA DATOS REMITENTE Y DESTINATARIO -->

            <div class="row"> <!-- FILA ASUNTO Y DESCRIPCION -->

                <div class="container-fluid">

                    <div id="divBuscar3" align="center">
                        <strong>DETALLE</strong>
                    </div>
                    <br>
                    <div class="col col-md-12">
                        <p class="text-danger"><strong>Asunto:</strong></p>
                        <p> {{ pqrdEcontrada.asunto_unidad_documental }}</p>
                        <br>
                        <p class="text-danger"><strong>Observaciones:</strong></p>
                        <p> {{ pqrdEcontrada.observaciones_unidad_documental }}</p>
                    </div>
                </div>

            </div> <!-- FIN ASUNTO Y DESCRIPCION-->

        </div> <!-- fin card body -->

    </div> <!-- fin card -->

</div> <!-- fin container fluid -->

<!--
<br>

<pre>   {{ pqrdEcontrada | json }}    </pre>

-->

<br>

<!--  <div ng-include="pqrdBotones"></div>  BOTONES GUARDAR Y CANCELAR -->
<?php $this->load->view('pqrd/pqrdTemplates/pqrdBotones') ?> <!-- BOTONES GUARDAR Y CANCELAR -->
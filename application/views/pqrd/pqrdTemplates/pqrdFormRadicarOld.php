<form name="formPqrd" ng-submit="guardarPqrd()" novalidate="novalidate"> <!-- FORMULARIO DE PQRD -->
    <div class="container-fluid">

        <!--  <div ng-include="pqrdBotones"></div>  BOTONES GUARDAR Y CANCELAR -->
        <?php $this->load->view('pqrd/pqrdTemplates/pqrdBotones') ?> <!-- BOTONES GUARDAR Y CANCELAR -->

        <br>

        <div class="container-fluid"> <!-- CARDS PARA RADICAR -->
            <div class="row"> <!-- PRIMERA FILA --> 

                    <div class="col-md-6" align="left"> <!-- PRIMERA COLUMNA --> 
                        <div class="card">  <!-- CARD PARA INFO CONTACTO -->
                            <div id="cardPqrdHeader" class="card-header">   
                                <h5 class="card-title">
                                    <i class="fas fa-info"></i> 
                                    <strong> INFORMACION DEL CONTACTO... </strong>
                                </h5>   
                            </div>
                            <div class="card-body" id="cardPqrdBody">
                                <br>
                            <!-- <form name="contactoForm" novalidate="novalidate">  FORMULARIO PARA INFO CONTACTO -->

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="tipoIden" class="control-label">Tipo de indentificacion</label>
                                        <select type="select" class="form-control" id="tipoIden"
                                            ng-model="pqrd.tipoIdentificacion"
                                            required
                                        >
                                            <option ng-value="$index" ng-repeat="tipoIdentificacion in tipoIdentificaciones.data"> {{ tipoIdentificacion.nom }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="iden" class="control-label">indentificacion</label>
                                        <input type="number" class="form-control" id="iden"
                                            placeholder="Digite su ID"
                                            ng-model="pqrd.numeroIdentificacion"
                                            ng-minlength="6"
                                            ng-maxlength="15"
                                            ng-blur="mostrarUsuario()"
                                            required 
                                        >
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="nombre" class="control-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" 
                                            placeholder="Digite su nombre"
                                            ng-model="pqrd.nombre"
                                            ng-minlength="2"
                                            ng-maxlength="30"
                                            required 
                                        >
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="apellido" class="control-label">Apellido</label>
                                        <input type="text" class="form-control" id="apellido"
                                            placeholder="Digite su apellido"
                                            ng-model="pqrd.apellido"
                                            ng-minlength="2"
                                            ng-maxlength="30"
                                            required
                                        > 
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="telefono" class="control-label">Telefono</label>
                                        <input type="number" class="form-control" id="telefono"
                                            placeholder="Digite su telefono"
                                            ng-model="pqrd.telefono"
                                            ng-minlength="10"
                                            ng-maxlength="15"
                                            required
                                        >
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="direccion" class="control-label">Direccion</label>
                                        <input type="text" class="form-control" id="direccion"
                                            placeholder="Digite su Direccion"
                                            ng-model="pqrd.direccion"
                                            ng-minlength="5"
                                            ng-maxlength="50"
                                            required
                                        > 
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="correo" class="control-label">Correo</label>
                                        <input type="email" class="form-control" id="correo" 
                                            placeholder="Digite su correo"
                                            ng-model="pqrd.email"
                                            required
                                        >
                                    </div>
                                </div>


                                <div class="row">

                                    <div class="container" ng-style="validarUi" >
                                        <label>Municipio de residencia</label>
                                    </div>

                                    <div class="form-group col-md-12"> <!-- <div class="form-group col-md-12">  -->
                                        <ui-select ng-model="pqrd.ubicacion" ng-click="txtVal()">  <!-- ng-disabled="disabled"  -->
                                            <ui-select-match>
                                                <span ng-bind="$select.selected.nom"></span> <!-- lo que queda al escoger -->
                                            </ui-select-match>
                                            <ui-select-choices repeat="departamentoConMunicipios in (departamentosConMunicipios.data | filter: $select.search) track by departamentoConMunicipios.id">
                                                <span ng-bind="departamentoConMunicipios.nom"></span>  <!-- lo que sale para escoger -->
                                            </ui-select-choices>
                                        </ui-select>
                                        <div ng-style="validarUi" ng-show="txtValidarUi">
                                            <label >Este campo es requerido</label>
                                        </div>
                                    </div>
                                </div>   
                            </div>

                        </div> <!-- FIN para card de contacto -->

                        <div class="card" align="left"> <!-- CARD CLASIFICACION PQRD -->
                            <div id="cardPqrdHeader" class="card-header">   
                                <h5 class="card-title">
                                    <i class="fas fa-grip-horizontal"></i>
                                    <strong> CLASIFICACION... </strong>
                                </h5>   
                            </div>

                            <div id="cardPqrdBody" class="card-body">
                                <br>
                            
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="tipo" class="control-label">Tipo</label>
                                        <select class="form-control" 
                                            id="tipo"
                                            ng-model="pqrd.tipoDoc"
                                            required
                                        >
                                            <option ng-value="$index" ng-repeat="tipoDocumento in tipoDocumentos.data">{{ tipoDocumento.nom }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="area" class="control-label">Area/Dependencia</label>
                                        <select class="form-control" 
                                            id="area"
                                            ng-model="pqrd.dependencias"
                                            required
                                        >
                                            <option ng-value="$index" ng-repeat="dependencia in dependencias.data">{{ dependencia.nom }}</option>
                                        </select>
                                    </div>
                                </div>                       
                            </div>
                            <br>
                        </div><!-- FIN card clasificacion pqrdTemplates -->
                    </div> <!-- FIN primera columna -->            

                    <div class="col-md-6" align="left"> <!-- SEGUNDA COLUMNA -->
                        <div class="card"> <!-- CARD DETALLE -->
                            <div id="cardPqrdHeader" class="card-header">   
                                <h5 class="card-title">
                                    <i class="fas fa-comments"></i>
                                    <strong> DETALLE PQRD... </strong>
                                </h5>   
                            </div>
                            <div id="cardPqrdBody" class="card-body">
                                <br>
                            
                                <div class="form-group">
                                    <label for="asunto" class="control-label">Asunto/Referencia</label>
                                    <input type="text" class="form-control" 
                                        id="asunto"
                                        ng-model="pqrd.asunto" 
                                        ng-minlength="3"
                                        ng-maxlength="200"
                                        required
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="descripcion" class="control-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" rows="3"
                                        ng-model="pqrd.descripcion"
                                        ng-minlength="8"
                                        ng-maxlength="2500"
                                        required
                                    >

                                    </textarea>
                                </div>
                            </div>
                        </div><!-- FIN card detalle -->

                        <br>
                        <br>

                        <div class="container-fluid"> <!-- tenga en cuenta -->
                            <div class=" container-fluid bg-primary text-white">
                                <br>
                                <p align="center">
                                    <i class="fas fa-info"></i>
                                    <strong> {{ definiciones.definiciones[6].tipo }}  </strong>
                                </p>
                                <p align="center"> {{ definiciones.definiciones[6].definicion }} </p>
                                <p align="center"> {{ definiciones.definiciones[6].definicion2 }} </p>
                                <br>
                            </div>
                        </div> <!-- FIN tenga en cuenta -->

                    </div> <!-- FIN segunda columna -->
                
            </div> <!-- FIN primera fila -->

            <br>
        </div> <!-- CARDS para radicar -->

        <?php $this->load->view('pqrd/pqrdTemplates/pqrdBotones') ?> <!-- BOTONES GUARDAR Y CANCELAR -->

    </div>

</form> <!-- FIN formulario pqrdTemplates -->

    
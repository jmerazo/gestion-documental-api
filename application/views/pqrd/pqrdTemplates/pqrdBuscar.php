<div class="col-sm-6">
	<div id="bordePer" align="left">
	    <div id="cardHeader" class="card-header">	
			<h5 class="card-title">
				<i class="fas fa-grip-horizontal"></i>
				<strong> BUSCAR... </strong>
			</h5>	
		</div>

		<div id="cardBody" class="card-body">
			<br>

            <form name="formBuscar" ng-submit="opcionBuscar()" novalidate="novalidate"> <!-- FORMULARIO DE BUSQUEDA PQRD -->
                  <div class="form-group row">
                    <label  class="col-sm-4 col-form-label">Número de radicado</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" placeholder="Digite el número de radicado"
                             ng-model="buscar.radicado"
                             required>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Código</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" placeholder="Digite el código"
                             ng-model="buscar.codigo"
                             required>
                    </div>
                  </div>

                    <div align="center">
                        <button type="ng-submit" class="btn btn-success" "> <!-- ng-click="opcionBuscar() -->
                            <i class="fas fa-search"></i>
                            <strong> Buscar</strong>
                        </button>
                    </div>
                <br>
            </form>
	    </div>
	</div>
</div>
  


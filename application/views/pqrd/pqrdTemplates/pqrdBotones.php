
<div class="row" align="right"> <!-- BOTONES GUARDAR Y CANCELAR -->
    <button type="ng-submit" class="btn btn-primary btn-lg" ng-disabled="saving" ng-click="guardarPqrd()" ng-show="btnGuardar">
        <i class="fas fa-save"></i> {{textBtnSaved}}
    </button>
    <button type="reset" class="btn btn-warning btn-lg" ng-click="opcionCancelar()" ng-show="btnCancelar">
        <i class="fas fa-undo-alt"></i> CANCELAR
    </button>

    <button type="reset" class="btn btn-warning btn-lg" ng-click="opcionCancelar()" ng-show="btnVolver">
        <i class="fas fa-undo-alt"></i> VOLVER
    </button>
</div> <!-- FIN botones guardar y cancelar -->

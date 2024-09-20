var app = angular.module('pqrdApp',['ngRoute','jcs-autoValidate','ui.select', 'ngSanitize' ]);

angular.module('jcs-autoValidate')
.run([
    'defaultErrorMessageResolver',
    function (defaultErrorMessageResolver) {
        // To change the root resource file path
        defaultErrorMessageResolver.setI18nFileRootPath(RUTA+'vendors/angular/angular-auto-validate'); //cambiar idioma a spanish
        defaultErrorMessageResolver.setCulture('es-co');
    }
]);


app.controller('mainPqrdCtrl', ['$scope','$http', function($scope,$http){

	//$scope.menuSuperior = 'application/views/pqrd/menu.php';


	$scope.setActive = function(Opcion){

		$scope.mInicio  = "";
		$scope.mPqrd    = "";

		$scope[Opcion] = "active";

	}

}]);
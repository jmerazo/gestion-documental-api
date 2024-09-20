app.config(function ($routeProvider) {

    $routeProvider
        /*.when('/home', {
            //templateUrl: 'application/views/pqrd/inicio.php',
            templateUrl: RUTA + 'pqrd/inicio/dash',
            controller: 'inicio.controller'
        })*/
        .when('/pqrd', {
            //templateUrl: 'application/views/pqrd/pqrd.php',
            templateUrl: RUTA + 'pqrd/inicio/pqrd',    /// ruta + pqrd que es carpeta controlador php/ nombre controlador/ funcion
            controller: 'pqrd.controller'
        })
        .otherwise({
            redirectTo: '/pqrd'
        });


});


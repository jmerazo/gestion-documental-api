app.controller('pqrd.controller', ['$scope', '$routeParams', '$http', '$q',
    function ($scope, $routeParams, $http, $q) {
        //----------------------------
        // CONTROL DE PESTAÑA ACTIVA
        //----------------------------

        $scope.setActive("mPqrd");

        //----------------------------
        // VARIABLES
        //----------------------------
        $scope.tab = 1;
        $scope.bandera = false;
        $scope.banderaSolicitudDesplazamiento = false; //Si es true se esta solicitando certificado de desplazamiento
        $scope.remiteUsu = true; // si es true remite persona si es false remite entidad
        $scope.opcRadicar = false;
        $scope.btnGuardar = true;
        $scope.btnCancelar = true;
        $scope.btnVolver = true;
        $scope.opcBuscar = false;
        $scope.txtValidarUi = false;
        $scope.saving = false;

        $scope.textBtnSaved = 'GUARDAR';

        //----------------------------
        // ARREGLOS
        //----------------------------
        $scope.definiciones = {};
        $scope.departamentosConMunicipios = {};
        $scope.dependencias = {};
        $scope.tipoIdentificaciones = {};
        $scope.tipoDocumentos = {};
        $scope.pqrdEcontrada = {};

        $scope.validarUi = {
            "color": "black"
        };

        $scope.buscar = {
            codigo: '',
            radicado: ''
        };

        $scope.pqrd = {
            tipoIdentificacion: '',
            numeroIdentificacion: '',
            nombre: '',
            apellido: '',
            telefono: '',
            direccion: '',
            ubicacion: {
                id: '',
                nom: ''
            },
            tipoDoc: '',
            dependencias: '',
            idUbicacion: '',
            asunto: '',
            descripcion: '',
            exception: '',
        };

        //SOCKET


        //----------------------------
        // RESETEAR PQRD
        //----------------------------
        function resetarPqrd() {

            $scope.btnGuardar = true;
            $scope.btnCancelar = true;
            $scope.btnVolver = true;
            $scope.bandera = false;
            $scope.saving = false;
            $scope.remiteUsu = true;
            $scope.file = null;

            $scope.buscar = {
                codigo: '',
                radicado: ''
            };

            $scope.pqrd = {
                tipoIdentificacion: '',
                numeroIdentificacion: '',
                nombre: '',
                apellido: '',
                telefono: '',
                direccion: '',
                ubicacion: {
                    id: '',
                    nom: ''
                },
                tipoDoc: '',
                dependencias: '',
                idUbicacion: '',
                asunto: '',
                descripcion: '',
                exception: ''
            };

            $scope.validarUi = { // al validar ui y no recargar queda rojo o verde segun lo antes echo en radicar
                "color": "black"
            };
        }

        //----------------------------
        // MANEJO DE OPCIONES
        //----------------------------

        $scope.opcionRadicar = function () {

            $scope.bandera = true;
            $scope.opcRadicar = true;
            $scope.opcBuscar = false;
            $scope.btnGuardar = true;
            $scope.btnCancelar = true;
            $scope.btnVolver = false;

        };

        $scope.opcionBuscar = function () {

            //console.log("opc buscar");
            // poner aqui una funcion que devuelva si encontro la pqrd para poder avanzar

            var aux = $scope.buscarPqrd().then(
                function (datos) {
                    //console.log(datos);
                    if (datos.dtsTercero === null) {

                        Swal.fire({
                            title: 'Oops...',
                            html: 'NO se encuentra la PQRD con los datos ingresados!',
                            type: 'error'
                        });

                    } else {
                        $scope.flagDefPqrd = true;
                        //console.log("si encontro la pqrd");
                        //console.log( JSON.stringify(datos) );

                        $scope.pqrdEcontrada = datos;

                        if ($scope.pqrdEcontrada.fecha_limite_respuesta == null) {
                            $scope.pqrdEcontrada.fecha_limite_respuesta = "N/A";
                        }

                        if ($scope.pqrdEcontrada.observaciones_unidad_documental == null) {
                            $scope.pqrdEcontrada.observaciones_unidad_documental = "Ninguna";
                        }

                        if ($scope.pqrdEcontrada.dtsTercero.nombre == "") {
                            $scope.remiteUsu = false; // si es true remite persona si es false remite entidad
                        }

                        $scope.bandera = true;
                        $scope.opcBuscar = true;
                        $scope.opcRadicar = false;
                        $scope.btnGuardar = false;
                        $scope.btnCancelar = false;
                        $scope.btnVolver = true;

                    }

                },
                function (error) {

                    console.log(" al traer datos con la promesa se genero el siguiente error-> " + error);
                }
            );

        };

        $scope.opcionCancelar = function () {

            $scope.bandera = false;
            $scope.flagDefPqrd = false;
            $scope.opcBuscar = false;
            $scope.opcRadicar = false;
            //$scope.btnGuardar   = true;
            resetarPqrd();

        };

        //----------------------------
        // OBTENER DEFINICIONES
        //----------------------------

        $http({method: "GET", url: RUTA + 'app/definiciones.json'})
            .then(
                function (datos) {
                    $scope.definiciones = datos.data;

                },
                function (error) {
                    console.error("no se pudo obtener los datos de las definiciones");
                }
            );
        //-----------------------------------
        // OBTENER TIPOS DE INDENTIFICACION
        //-----------------------------------

        $http({method: "GET", url: RUTA + 'pqrd/datos_pqrd/combo'})
            .then(
                function (datos) {
                    $scope.tipoIdentificaciones = datos.data;
                    //console.log($scope.tipoIdentificaciones);
                },
                function (error) {
                    console.error("no se pudo obtener los datos de las identificaciones");
                }
            );

        //-----------------------------------
        // OBTENER TIPOS DE PQRD
        //-----------------------------------

        $http({method: "GET", url: RUTA + 'pqrd/datos_pqrd/comboPqrd'})
            .then(
                function (datos) {
                    $scope.tipoDocumentos = datos.data;
                    //console.log($scope.tipoDocumentos);
                },
                function (error) {
                    console.error("no se pudo obtener los datos de los tipos de documento");
                }
            );

        //-----------------------------------
        // OBTENER DEPARTAMENTOS Y MUNICIPIOS
        //-----------------------------------

        $http({method: "GET", url: RUTA + 'pqrd/datos_pqrd/comboDivipola'})
            .then(
                function (datos) {
                    $scope.departamentosConMunicipios = datos.data;
                    //console.log($scope.departamentosConMunicipios.data[0].id);
                },
                function (error) {
                    console.error("no se pudo obtener los datos de los departamentos y municipios");
                }
            );

        //-----------------------------------
        // OBTENER DEPENDENCIAS
        //-----------------------------------

        $http({method: "GET", url: RUTA + 'pqrd/datos_pqrd/comboDependencias'})
            .then(
                function (datos) {
                    $scope.dependencias = datos.data;
                    //console.log($scope.dependencias);
                },
                function (error) {
                    console.error("no se pudo obtener los datos de las depedencias");
                }
            );

        //-----------------------------------
        // OBTENER EXCEPCIONES
        //-----------------------------------

        $http({method: "GET", url: RUTA + 'pqrd/datos_pqrd/exceptions'})
            .then(
                function (datos) {
                    $scope.exceptions = datos.data;
                    //console.log($scope.dependencias);
                },
                function (error) {
                    console.error("no se pudo obtener los datos de las depedencias");
                }
            );

        //-----------------------------------
        // OBTENER DATOS DE PQRD DESDE BD
        //-----------------------------------

        $scope.buscarPqrd = function () {

            //console.log("numero radicado:  "+$scope.buscar.radicado );
            //console.log("codigo pin:  "+$scope.buscar.codigo );

            var d = $q.defer();

            $http({
                method: "GET",
                url: RUTA + 'pqrd/datos_pqrd/buscarPqrd' + '/' + $scope.buscar.radicado + '/' + $scope.buscar.codigo
            })
                .then(function (datos) {
                        //console.log(JSON.stringify(datos.data.data));
                        d.resolve(datos.data.data)
                    },
                    function (error) {
                        console.error("ocurrio un error al traer los datos de la pqrd");
                        d.reject(error)
                    });

            return d.promise;


        };

        //-----------------------------------
        // OBTENER DATOS DE USUARIO DESDE BD
        //-----------------------------------

        $scope.mostrarUsuario = function () {

            //console.log("numero iden:  "+$scope.pqrd.numeroIdentificacion);
            //console.log("tipo iden:  "+$scope.pqrd.tipoIdentificacion);

            if ($scope.pqrd.tipoIdentificacion === "" || $scope.pqrd.numeroIdentificacion === "" || $scope.pqrd.tipoIdentificacion === null || $scope.pqrd.numeroIdentificacion === null) {

                console.error("tipo ide o nit tercero son nulos o estan vacios");
                //console.error("tipo ide es: "+$scope.pqrd.numeroIdentificacion+" nit tercero es: "+$scope.pqrd.tipoIdentificacion);

            } else {

                $http({
                    method: "GET",
                    url: RUTA + 'comun/externos/buscaTerceroXDocumento' + '/' + $scope.pqrd.tipoIdentificacion + '/' + $scope.pqrd.numeroIdentificacion
                })
                    .then(function (datos) {
                            //console.log(JSON.stringify(datos));
                            //console.log(datos.data.data);
                            if (datos.data.data === null) {
                                console.log(" no hay pqrd en la BD con el tipo y numero de indentificacion ingresados");

                            } else {

                                $scope.pqrd = {
                                    tipoIdentificacion: $scope.pqrd.tipoIdentificacion,
                                    numeroIdentificacion: $scope.pqrd.numeroIdentificacion,
                                    nombre: datos.data.data.nombres_tercero,
                                    apellido: datos.data.data.apellidos_tercero,
                                    telefono: datos.data.data.cel_fijo_tercero,
                                    direccion: datos.data.data.direccion_tercero,
                                    email: datos.data.data.mail_tercero,
                                    idUbicacion: datos.data.data.ide_divipola_tercero,
                                    ubicacion: {
                                        id: datos.data.data.ide_divipola_tercero,
                                        nom: buscardep(datos.data.data.ide_divipola_tercero)
                                    }

                                };

                            }

                        },
                        function (error) {
                            console.error("no se pudo obtener los datos del usuario");
                        });

            }

        };

        //-----------------------------------
        // SUPERVISO CAMBIOS EN TIPO PQRD
        //-----------------------------------
        $scope.$watch('pqrd.tipoDoc', function (vnew, vold) {
            if (vnew === 37) {
                $scope.banderaSolicitudDesplazamiento = true;
                $scope.textBtnSaved = 'GENERAR DOCUMENTO';
            } else {
                $scope.banderaSolicitudDesplazamiento = false;
                $scope.textBtnSaved = 'GUARDAR';
            }
        });


        //-----------------------------------
        // ENVIAR DATOS A BD
        //-----------------------------------
        function enviarDatosDb() {
            //console.log("datos a enviar: "+ JSON.stringify($scope.pqrd));
            $scope.saving = true;
            Swal.showLoading();
            const $archivos = document.querySelector("#archivo");

            console.log($archivos.files);

            var fd = new FormData();
            fd.append('request', JSON.stringify($scope.pqrd));

            //Take the first selected file
            // Agregar cada archivo al formdata
            fd.append('file', $archivos.files[0]);
            /*
                        angular.forEach($archivos.files, function (archivo) {
                            fd.append('file', archivo);
                        });
            */

            // Primero la configuración
            let configuracion = {
                headers: {
                    "Content-Type": undefined,
                },
                transformRequest: angular.identity,
            };
            // Ahora sí
            var d = $q.defer();

            $http
                .post(RUTA + 'pqrd/datos_pqrd/guardarPqrd', fd, configuracion)
                .then(function (respuesta) {
                    console.log("Después de enviar los archivos, el servidor dice:", respuesta.data);
                    Swal.close();
                    d.resolve(respuesta)
                })
                .catch(function (detallesDelError) {
                    console.warn("Error al enviar archivos:", detallesDelError);
                    Swal.close();
                    d.reject(detallesDelError)

                })

            return d.promise;
            /*
             $http({method: "POST", url: RUTA + 'pqrd/datos_pqrd/guardarPqrd', fd})
                 .then(
                     function (datax) {
                         console.log("la respuesta de guardar pqrd: " + datax);
                     }/*,
                     function (err) {
                     }
                 );*/
        }

        //---------------------------------------------------
        // OBTENER NOMBRE DE UBICACION A PARTIR DE ID
        //---------------------------------------------------

        function buscardep(idDep) {
            for (var i = 0; i < $scope.departamentosConMunicipios.data.length; i++) {
                if ($scope.departamentosConMunicipios.data[i].id === idDep) {
                    //console.log("SI lo encontro dep en posicion: "+i);
                    return $scope.departamentosConMunicipios.data[i].nom;
                }
            }
            //console.log("no encontro dep");
        }

        //---------------------------------------------------
        // VALIDACION DE UI-SELECT (NO FUNCIONA CON EL autovalidate NORMAL ni con jcs)
        //---------------------------------------------------

        $scope.txtVal = function () {
            console.log("entra a txt val ");
            if ($scope.pqrd.ubicacion.id === '') {
                $scope.txtValidarUi = true;
                $scope.validarUi = {
                    "color": "#9B342D"
                }
				console.log("entra por if")

            } else {
                $scope.txtValidarUi = false;
                $scope.validarUi = {
                    "color": "green"
                }
				console.log("entra por else")
            }
        };

        //---------------------------------
        //  FUNCION PARA RECARGAR LA PAGINA
        //---------------------------------

        function recargarPagina() {
            window.location.reload();
        };


        //----------------------------
        // GUARDAR DATOS EN BD DE PQRD
        //----------------------------

        $scope.guardarPqrd = function () {

            $scope.txtValidarUi = false;
            $scope.validarUi = {
                "color": "green"
            }
			
			console.log("Validate");

            Swal.fire({
                title: 'Esta seguro de enviar la PQRD?',
                text: "No podra editar los datos luego de enviar!",
                type: 'warning',
                allowOutsideClick: false,
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'SI, Enviar!'
            }).then((result) => {
                if (result.value) {
                    enviarDatosDb().then(function (data) {
                        //console.log("radicado de la pqrd : "+data.data.nro_radicado);
                        //console.log("pin de la pqrd : "+data.data.pin_pqrd);
                        //console.log("Datos enviados a BD de pqrd: " + $scope.pqrd);
                        //console.log($scope.pqrd, data);
                        if ($scope.pqrd.exception.toString().length > 0 && $scope.pqrd.exception != 9999) {
                            var rutanew = RUTA + 'pqrd/datos_pqrd/getDocumentRtaDesplazamiento/' + data.data.nro_radicado;
                            window.open(rutanew, '_blank');
                            Swal.fire({
                                title: 'ENVIADO CORRECTAMENTE!',
                                html: 'POR FAVOR guarde los siguientes datos para buscar posteriormente su PQRD. Número de radicado que es: <strong>' + data.data.nro_radicado + '</strong>.<br><a href="' + rutanew + '" target="_blank">Click para descargar documento</a>.<br>  Gracias por usar nuestro servicio de PQRD',
                                type: 'success',
                                allowOutsideClick: false,
                            });
                        } else if ($scope.pqrd.exception == 9999) {
                            Swal.fire({
                                title: 'ENVIADO CORRECTAMENTE!',
                                html: 'Con base en la informacion por usted remitida, para el caso que nos ocupa se observa que las circunstancias, descritas en la solicitud no se encuentra  dentro de ninguna de las 43 excepciones que plasman los  casos o actividades que se permiten el derecho de circulación de las personas, establecidas en el decreto 749 del 28 de mayo del 2020<br><br>POR FAVOR guarde los siguientes datos para buscar posteriormente su PQRD. Número de radicado que es: <strong>' + data.data.nro_radicado + '</strong> y el pin que es: <strong>' + data.data.pin_pqrd + '</strong>, Gracias por usar nuestro servicio de PQRD',
                                type: 'success',
                                allowOutsideClick: false,
                            });

                        } else {
                            Swal.fire({
                                title: 'ENVIADO CORRECTAMENTE!',
                                html: 'POR FAVOR guarde los siguientes datos para buscar posteriormente su PQRD. Número de radicado que es: <strong>' + data.data.nro_radicado + '</strong> y el pin que es: <strong>' + data.data.pin_pqrd + '</strong>, Gracias por usar nuestro servicio de PQRD',
                                type: 'success',
                                allowOutsideClick: false,
                            });
                        }


                        resetarPqrd();
                        $scope.bandera = false;
                    }, function (err) {
                        console.log('error ******', err)
                    });

                }
            })
        };
        //----------------------------
        // MANEJO DE TAGS
        //----------------------------

        $scope.setTab = function (newTab) {
            $scope.tab = newTab;
        };

        $scope.isSet = function (tabNum) {
            return $scope.tab === tabNum;
        };

    }]);
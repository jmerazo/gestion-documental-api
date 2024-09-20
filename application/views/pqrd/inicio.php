<!DOCTYPE html>

<html ng-app="pqrdApp" ng-controller="mainPqrdCtrl">
    <head>
        
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>PQRD</title>

        <!-- Incluir Bootstrap -->
        <link rel="stylesheet" href="<?php echo base_url() ?>vendors/libJs/bootstrap/dist/css/bootstrap.min.css">

        <!-- Animate.css y Font-Awesome.css -->
        <link rel="stylesheet" href="<?php echo base_url() ?>vendors/css/animate.css">
        <link rel="stylesheet" href="<?php echo base_url() ?>vendors/libJs/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo base_url() ?>vendors/libJs/font-awesome/css/all.min.css">

        <!-- Estilo personalizado -->
        <link rel="stylesheet" href="<?php echo base_url() ?>vendors/css/styles.css">

        <!--Incluir sweet alert -->
        <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>vendors/libJs/sweet-alert2/dist/sweetalert2.css">

        <!--Incluir ui-select -->
        <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>vendors/angular/angular-ui-select/select.min.css">

        <script>
            var RUTA='<?php echo base_url() ?>';

        </script>



        <!-- Incluir AngularJS -->
        <script src="<?php echo base_url() ?>vendors/angular/angular.min.js"></script>
        <script src="<?php echo base_url() ?>vendors/angular/angular-route/angular-route.min.js"></script>
        <script src="<?php echo base_url() ?>vendors/libJs/sweet-alert2/dist/sweetalert2.js"></script>
        <script src="<?php echo base_url() ?>vendors/angular/angular-auto-validate/jcs-auto-validate.min.js"></script>
        <script src="<?php echo base_url() ?>vendors/angular/angular-ui-select/select.min.js"></script>
        <script src="<?php echo base_url() ?>vendors/angular/angular-ui-select/sanitize.js"></script>

        <script src="<?php echo base_url() ?>app/app.js"></script>
        <script src="<?php echo base_url() ?>app/config.js"></script>

        <!-- Controladores de páginas -->
        <script src="<?php echo base_url() ?>app/pqrd/inicio.controller.js"></script>
        <script src="<?php echo base_url() ?>app/pqrd/pqrd.controller.js"></script>

            </head>
    <body>

        <?php //$this->load->view('pqrd/menu') ?>
        <!-- <div ng-include="menuSuperior"></div>  -->

        <div class="container">
           
            <div ng-view></div>

        </div>


    </body>
</html>

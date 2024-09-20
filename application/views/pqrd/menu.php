<nav class="navbar navbar-inverse navbar-fixed-top"> <!-- nav contiene codigo de un menu de navegacion -->
    <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="">
            <i class="fas fa-home"></i> Pqrd App
          </a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li ng-class="mInicio"><a href="#!/home">Inicio</a></li>
            <li ng-class="mPqrd">
              <a href="#!/pqrd"> <!-- lo que sale en la barra url -->
                <i class="fas fa-file"></i> Pqrd
              </a>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
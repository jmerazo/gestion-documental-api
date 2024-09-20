<div class="container-fluid">
	<ul class="nav nav-tabs ">
	   <li ng-class="{ active: isSet(1) }">
	     <a href ng-click="setTab(1)"> {{ definiciones.definiciones[2].tipo }} </a>
	   </li>
	   <li ng-class="{ active: isSet(2) }">
	     <a href ng-click="setTab(2)"> {{ definiciones.definiciones[3].tipo }} </a>
	   </li>
	   <li ng-class="{ active: isSet(3) }">
	     <a href ng-click="setTab(3)"> {{ definiciones.definiciones[4].tipo }} </a>
	   </li>
	   <li ng-class="{ active: isSet(4) }">
	     <a href ng-click="setTab(4)"> {{ definiciones.definiciones[5].tipo }} </a>
	   </li>
	</ul>

	<div class="jumbotron jumbotron-fluid" id="jumbotronTabs">
		<div class="container-fluid">
		 	<div ng-show="isSet(1)">
		    	<p class="text-dark" id="def"> {{ definiciones.definiciones[2].definicion }} </p>
		  	</div>
		  	<div ng-show="isSet(2)">
		    	<p class="text-dark" id="def"> {{ definiciones.definiciones[3].definicion }} </p>  
		  	</div>
		  	<div ng-show="isSet(3)">
		    	<p class="text-dark" id="def"> {{ definiciones.definiciones[4].definicion }} </p>  
		  	</div>
		  	<div ng-show="isSet(4)">
		   		<p class="text-dark" id="def"> {{ definiciones.definiciones[5].definicion }} </p> 
		  </div>
		</div>
	</div>

	<div id="tabs" ng-controller="pqrd.controller">
        <ul>
            <li ng-repeat="tab in tabs" 
                ng-class="{active:isActiveTab(tab.url)}" 
                ng-click="onClickTab(tab)">{{tab.title}}
            </li>
        </ul>
        <div id="mainView">
            <div ng-include="currentTab"></div>
        </div>
    </div>
</div>
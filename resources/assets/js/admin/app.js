window._ = require('lodash');
window.$ = window.jQuery = require('jquery');
// window.pleaseWait = require('./../../../../node_modules/please-wait/build/please-wait.min.js');
require('./layout/nav.js')
require('angular');
require('bootstrap-sass');
require('angular-acl');
require('angular-animate');
require('angular-loading-bar');
require('angular-sanitize');
require('angular-sweetalert');
require('angular-ui-bootstrap');
require('angular-ui-router');
require('angular-toastr');
require('ng-file-upload');
require('satellizer');
require('sweetalert');
require('ui-select');
require('angular-recaptcha');

window.XLS = require('xlsx-browserify-shim');

angular.module('app', [
  'angular-loading-bar',
  'ngAnimate',
  'ngFileUpload',
  'ngSanitize',
  'mm.acl',
  'oitozero.ngSweetAlert',
  'satellizer',
  'toastr',
  'ui.bootstrap',
  'ui.router',
  'ui.select',
  'vcRecaptcha',
])
.run(function($rootScope, $log, $auth, $state, toastr) {
	$rootScope.$on('$stateChangeError',
		function(event, toState, toParams, fromState, fromParams, error) {
      if (error.status == 401) {
        $auth.removeToken();
        toastr.error(error.data.error, 'Estado!');
        $state.go('login');
      }
			$log.error('error', error);
		});
})
.config(require('./routes.js'))
.config(function($authProvider, uibPaginationConfig) {
	$authProvider.loginUrl = '/login';
  uibPaginationConfig.previousText = 'Previo';
  uibPaginationConfig.nextText = 'Siguiente';
})
.directive('jsXls', require('./directives/angular-js-xlsx'))
.directive('smartMenu', require('./directives/smartMenu'))
.directive('stateBreadcrumbs', require('./directives/stateBreadcrumbs'))
.directive('ngEnter', require('./directives/NgEnter'))
.filter('isEmpty', require('./filters/IsEmpty'))
.filter('propsFilter', require('./filters/propsFilter'))
.controller('NavBarController', require('./app/NavBarController'))
.service('ApiService', require('./services/ApiService'))
.service('Confirm', require('./services/Confirm'))
.service('UserService', require('./users/UserService'))
.service('RoleService', require('./roles/RoleService'))
.service('PermissionService', require('./permissions/PermissionService'))
.service('RegionService', require('./regions/RegionService'))
.service('ServicioSaludService', require('./serviciosSalud/ServicioSaludService'))
.service('ComunaService', require('./comunas/ComunaService'))
.service('EstablecimientoService', require('./establecimientos/EstablecimientoService'))
.service('PuntoEntregaService', require('./puntosEntrega/PuntoEntregaService'))
.service('CategoriaService', require('./categorias/CategoriaService'))
.service('PoblacionService', require('./poblaciones/PoblacionService'))
.service('ProductoService', require('./productos/ProductoService'))
.service('EstimacionService', require('./estimaciones/EstimacionService'))
.service('AgnoService', require('./agno/AgnoService'))
.service('PedidoService', require('./pedidos/PedidoService'))
.service('ConfiguracionService', require('./configuracion/ConfiguracionService'))
.service('EstimacionManualService', require('./puntosEntrega/EstimacionManualService'))
.factory('PleaseWaitFactory', function() {
  return {
    show: function() {
      window.loadingPleaseWait = pleaseWait({
        logo: "/img/logo.png",
        backgroundColor: '#22262e',
        loadingHtml: '<p class="loading-message">Calculando estimaciones ...</p><div class="sk-fading-circle"> <div class=""></div><div class="sk-circle2 sk-circle"></div><div class="sk-circle3 sk-circle"></div><div class="sk-circle4 sk-circle"></div><div class="sk-circle5 sk-circle"></div><div class="sk-circle6 sk-circle"></div><div class="sk-circle7 sk-circle"></div><div class="sk-circle8 sk-circle"></div><div class="sk-circle9 sk-circle"></div><div class="sk-circle10 sk-circle"></div><div class="sk-circle11 sk-circle"></div><div class="sk-circle12 sk-circle"></div></div>'
      });
    },
    hide: function() {
      window.loadingPleaseWait.finish();
    }
  }
});


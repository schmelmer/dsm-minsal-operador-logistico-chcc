module.exports = function (data, regiones, region, responsables, RegionService, ServicioSaludService, ComunaService, PuntoEntregaService, $state, toastr, $stateParams) {
  'ngInject';
  var vm = this;
  vm.action = 'Editar';
  vm.data =  data;
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.responsables = responsables;
  vm.regiones = regiones;
  vm.servicios = region.servicios
  vm.comunas = _.find(vm.servicios, {id: data.servicio_id}).comunas;
  vm.establecimientos = _.find(vm.comunas, {id: data.comuna_id}).establecimientos;

  this.submitForm = function () {
    vm.formIsSubmit = true;

    PuntoEntregaService.updateResource($stateParams.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.puntosEntrega', {}, {reload: true});
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      }).finally(function() {
        vm.formIsSubmit = false;
      });
  };

  this.changeRegion = function () {
    vm.loadingServicios = true;
    RegionService.getResource(vm.data.region_id, 'servicios').then(function(data) {
      vm.loadingServicios = false;
      vm.servicios = data.data.servicios;
      vm.data.servicio_id = null;
      vm.data.comuna_id = null;
      vm.data.establecimiento_id = null;
    });
  };

  this.changeServicio = function () {
    if (vm.data.servicio_id) {
      vm.loadingComunas = true;
      ServicioSaludService.getResource(vm.data.servicio_id, 'comunas').then(function(data) {
        vm.loadingComunas = false;
        vm.comunas = data.data.comunas;
        vm.data.comuna_id = null;
        vm.data.establecimiento_id = null;
      });
    }
  };

  this.changeComuna = function () {
    if (vm.data.comuna_id) {
      vm.loadingEstablecimientos = true;
      ComunaService.getResource(vm.data.comuna_id, 'establecimientos').then(function(data) {
        vm.establecimientos = data.data.establecimientos;
        vm.loadingEstablecimientos = false;
      });
    }
  };

  this.hasError = function(property) {
    return vm.errors.hasOwnProperty(property);
  };
};

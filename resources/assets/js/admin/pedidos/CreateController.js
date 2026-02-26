module.exports = function (regiones, responsables, RegionService, ServicioSaludService, ComunaService, PuntoEntregaService, $state, toastr) {
  'ngInject';
  var vm = this;
  vm.action = 'Crear';
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.data = {};
  vm.regiones = regiones;
  vm.responsables = responsables;
  vm.loadingServicios = false;
  vm.loadingComunas = false;
  vm.loadingEstablecimientos = false;

  this.submitForm = function () {
    vm.formIsSubmit = true;

    PuntoEntregaService.createResource(vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.puntosEntrega.index', {}, {reload: true});
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

module.exports = function (EstablecimientoService, comunas, $state, toastr) {
  'ngInject';
  var vm = this;
  vm.action = 'Crear';
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.comunas = comunas;
  vm.data = {};
  vm.hospitales = [{id: 'h', name: 'Hospital'}, {id: 'a/h', name: 'APS/Hospital'}];

  this.submitForm = function () {
    vm.formIsSubmit = true;

    EstablecimientoService.createResource(vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.establecimientos.index', {}, {reload: true});
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      }).finally(function() {
        vm.formIsSubmit = false;
      });
  };

  this.hasError = function(property) {
    return vm.errors.hasOwnProperty(property);
  };

};

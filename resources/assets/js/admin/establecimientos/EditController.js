module.exports = function (data, comunas, EstablecimientoService, $state, toastr) {
  'ngInject';
  var vm = this;
  vm.action = 'Editar';
  vm.data =  data;
  vm.comunas = comunas;
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.hospitales = [{id: 'h', name: 'Hospital'}, {id: 'a/h', name: 'APS/Hospital'}];
  if (vm.data.hospital == null) {
    vm.data.hospital = '';
  }

  this.submitForm = function () {
    vm.formIsSubmit = true;

    EstablecimientoService.updateResource(vm.data.id, vm.data)
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

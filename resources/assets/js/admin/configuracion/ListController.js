module.exports = function (agno, agnos, distribuidor, distribuidores, AgnoService, ConfiguracionService, toastr, AclService) {
  'ngInject';
  var vm = this;
  vm.agnos = agnos;
  vm.distribuidores = distribuidores;
  vm.data = {agno: agno, distribuidor: distribuidor};
  vm.formIsSubmit = false;
  vm.formDistribuidorIsSubmit = false;
  vm.hasRole = AclService.hasRole;

  vm.submitForm = function() {
    vm.formIsSubmit = true;

    AgnoService.actualiza(vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      }).finally(function() {
        vm.formIsSubmit = false;
      });
  };

  vm.submitDistribuidorForm = function() {
    vm.formDistribuidorIsSubmit = true;

    ConfiguracionService.actualiza(vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
      })
      .catch(function(errors) {
        vm.errors = errors.data;
      }).finally(function() {
        vm.formDistribuidorIsSubmit = false;
      });
  };
};
;

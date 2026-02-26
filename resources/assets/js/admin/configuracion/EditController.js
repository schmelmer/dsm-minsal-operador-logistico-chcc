module.exports = function (agno, agnos, AgnoService) {
  'ngInject';
  var vm = this;
  vm.agnos = agnos;
  vm.data = {agno: agno};
  vm.formIsSubmit = false;

  vm.submit = function() {
    vm.formIsSubmit = true;

    AgnoService.actualiza(vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.configuracion', {}, {reload: true});
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      }).finally(function() {
        vm.formIsSubmit = false;
      });
  };
};

module.exports = function (CategoriaService, $state, toastr) {
  'ngInject';
  var vm = this;
  vm.action = 'Crear';
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.data = {
    name:  '',
    code: '',
  };

  this.submitForm = function () {
    vm.formIsSubmit = true;

    CategoriaService.createResource(vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.categorias.index', {}, {reload: true});
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

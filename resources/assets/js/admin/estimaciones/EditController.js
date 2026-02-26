module.exports = function (data, puntosEntrega, productos, EstimacionService, $state, toastr) {
  'ngInject';
  var vm = this;
  vm.action = 'Editar';
  vm.data =  data;
  vm.puntosEntrega = puntosEntrega;
  vm.productos = productos;
  vm.errors = {};
  vm.formIsSubmit = false;

  this.submitForm = function () {
    vm.formIsSubmit = true;

    EstimacionService.updateResource(vm.data.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.productos.index', {}, {reload: true});
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

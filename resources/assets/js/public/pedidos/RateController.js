module.exports = function (pedido, PedidoService, $state, toastr, $stateParams) {
  'ngInject';
  var vm = this;
  vm.pedido = pedido;
  vm.data =  {};
  vm.errors = {};
  vm.formIsSubmit = false;

  this.submitForm = function () {
    vm.formIsSubmit = true;

    PedidoService.rateResource($stateParams.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.pedidos.index', {}, {reload: true});
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

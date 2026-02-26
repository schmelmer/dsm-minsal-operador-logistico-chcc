module.exports = function (solicitud, PedidoService, $state, toastr, $stateParams) {
  'ngInject';
  var vm = this;
  console.log(solicitud);
  vm.data =  {observacion: solicitud.observacion_distribuidor};
  vm.errors = {};
  vm.formIsSubmit = false;

  this.submitForm = function () {
    vm.formIsSubmit = true;

    PedidoService.makeObservation($stateParams.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.solicitudes.index', {}, {reload: true});
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

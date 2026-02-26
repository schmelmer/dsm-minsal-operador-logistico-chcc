module.exports = function (puntosEntrega, territorios, PuntoEntregaService, toastr, Confirm, $state, $stateParams, AclService, PleaseWaitFactory) {
  'ngInject';
  var vm = this;
  vm.puntosEntrega = puntosEntrega.data;
  vm.territorios = territorios;
  vm.totalItems = puntosEntrega.total;
  vm.itemsPerPage = puntosEntrega.per_page;
  vm.search = $stateParams;
  vm.page = puntosEntrega.current_page;
  vm.reload = false;
  vm.hasRole = AclService.hasRole;

  vm.filter = function (page) {
    vm.search.page = page || 1;
    $state.go('.', vm.search, {reload: true});
    vm.reload = true;
  };

  vm.destroy = function (id, index) {
    Confirm.destroy(function() {
      PleaseWaitFactory.show();
      vm.deleteUser(id, index);
    });
  };

  vm.deleteUser = function (id, index) {
    PuntoEntregaService.deleteResource(id)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        PleaseWaitFactory.hide();
        vm.removeFromPuntosEntrega(index);
      })
      .catch(function(errors) {
        PleaseWaitFactory.hide();
        vm.errors = errors.data.errors;
      });
  };

  vm.removeFromPuntosEntrega = function (index) {
    vm.puntosEntrega.splice(index, 1);
  };
};

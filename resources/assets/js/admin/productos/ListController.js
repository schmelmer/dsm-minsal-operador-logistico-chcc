module.exports = function (productos, ProductoService, toastr, Confirm, $state, $stateParams, AclService) {
  'ngInject';
  var vm = this;
  vm.productos = productos.data;
  vm.totalItems = productos.total;
  vm.itemsPerPage = productos.per_page;
  vm.search = $stateParams;
  vm.page = productos.current_page;
  vm.reload = false;
  vm.hasRole = AclService.hasRole;
  vm.opcionesBooleanas = [
    {'id': '', 'value': 'Todos'},
    {'id': 1, 'value': 'Si'},
    {'id': 0, 'value': 'No'},
  ];

  vm.destroy = function (id, index) {
    Confirm.destroy(function() {
      vm.deleteProducto(id, index);
    });
  };

  vm.deleteProducto = function (id, index) {
    ProductoService.deleteResource(id)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.reload();
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      });
  };

  vm.filter = function (page) {
    vm.search.page = page || 1;
    $state.go('.', vm.search, {reload: true});
    vm.reload = true;
  };
};

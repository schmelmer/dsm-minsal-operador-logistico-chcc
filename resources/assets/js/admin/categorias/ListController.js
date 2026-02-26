module.exports = function (categorias, CategoriaService, toastr, Confirm, $state, $stateParams, AclService) {
  'ngInject';
  var vm = this;
  vm.categorias = categorias.data;
  vm.totalItems = categorias.total;
  vm.itemsPerPage = categorias.per_page;
  vm.search = $stateParams;
  vm.page = categorias.current_page;
  vm.reload = false;
  vm.hasRole = AclService.hasRole;

  vm.destroy = function (id, index) {
    Confirm.destroy(function() {
      vm.deleteCategoria(id, index);
    });
  };

  vm.deleteCategoria = function (id, index) {
    CategoriaService.deleteResource(id)
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

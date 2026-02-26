module.exports = function (poblaciones, PoblacionService, toastr, Confirm, $state, $stateParams, AclService) {
  'ngInject';
  var vm = this;
  vm.poblaciones = poblaciones.data;
  vm.totalItems = poblaciones.total;
  vm.itemsPerPage = poblaciones.per_page;
  vm.search = $stateParams;
  vm.page = poblaciones.current_page;
  vm.reload = false;
  vm.hasRole = AclService.hasRole;

  vm.destroy = function (id, index) {
    Confirm.destroy(function() {
      vm.deletePoblacion(id, index);
    });
  };

  vm.deletePoblacion = function (id, index) {
    PoblacionService.deleteResource(id)
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

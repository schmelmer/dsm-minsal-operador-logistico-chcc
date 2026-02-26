module.exports = function (estimaciones, EstimacionService, territorios, toastr, Confirm, $state, $stateParams, AclService) {
  'ngInject';
  var vm = this;
  vm.territorios = territorios;
  vm.estimaciones = estimaciones.data;
  vm.totalItems = estimaciones.total;
  vm.itemsPerPage = estimaciones.per_page;
  vm.search = $stateParams;
  vm.search.page = estimaciones.current_page;
  vm.reload = false;
  vm.hasRole = AclService.hasRole;

  vm.destroy = function (id, index) {
    Confirm.destroy(function() {
      vm.deleteEstimacion(id, index);
    });
  };

  vm.deleteEstimacion = function (id, index) {
    EstimacionService.deleteResource(id)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.reload();
      })
      .catch(function(errors) {
        vm.errors = errors.data;
      });
  };

  vm.filter = function () {
    $state.go('.', vm.search, {reload: true});
    vm.reload = true;
  };
};

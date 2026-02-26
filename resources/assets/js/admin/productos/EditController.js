module.exports = function (data, categorias, poblaciones, ProductoService, PoblacionService, $state, toastr, PleaseWaitFactory) {
  'ngInject';
  var vm = this;
  vm.action = 'Editar';
  vm.data =  Object.assign({}, data);
  vm.categorias = categorias;
  vm.poblaciones = poblaciones;
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.years = initYears();

  this.submitForm = function () {
    vm.formIsSubmit = true;

    if (! vm.data.de_servicios && ! vm.data.de_hospitales && (vm.data.poblacion_id != data.poblacion_id || vm.data.stock != data.stock || vm.data.cajas != data.cajas || vm.data.excluye_hospitales_secundarios != data.excluye_hospitales_secundarios)) {
      PleaseWaitFactory.show();
    }
    ProductoService.updateResource(vm.data.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.productos.index', {}, {reload: true});
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      }).finally(function() {
        vm.formIsSubmit = false;

        if (! vm.data.de_servicios && ! vm.data.de_hospitales && (vm.data.poblacion_id != data.poblacion_id || vm.data.stock != data.stock || vm.data.cajas != data.cajas ||  vm.data.excluye_hospitales_secundarios != data.excluye_hospitales_secundarios)) {
          PleaseWaitFactory.hide();
        }
      });
  };

  this.changeYear = function () {
    vm.loadingPoblaciones = true;
    PoblacionService.filterResources({year: vm.data.year}, false).then(function(data) {
      vm.loadingPoblaciones = false;
      vm.poblaciones = data.data;
      vm.data.poblacion_id = null;
    });
  };

  this.hasError = function(property) {
    return vm.errors.hasOwnProperty(property);
  };

  function initYears() {
    var years = [];
    var startYear = 2016;
    var endYear = new Date().getFullYear();

    for (var i = startYear; i < endYear + 4; i++) {
      years.push(i);
    }

    return years;
  }
};

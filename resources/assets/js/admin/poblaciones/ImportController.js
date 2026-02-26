module.exports = function (PoblacionService, establecimientos, $state, $stateParams, toastr, $scope, $http) {
  'ngInject';
  var vm = this;
  vm.action = 'Importar';
  vm.data = [];

  $scope.load = function() {
    $scope.$digest();
    vm.errorMessage = '';
    vm.data = [];
    vm.notFound = [];
    vm.loading = true;
  }

  $scope.read = function(workbook) {
    vm.loading = true;
    vm.data = toJson(workbook).Worksheet;
    vm.notFound = establecimientosNotFound();
    if (vm.notFound.length > 0) {
    } else {
      vm.submitForm();
    }
    vm.loading = false;
    $scope.$digest();
  }

  $scope.error = function(e) {
    vm.errorMessage = 'Comprueba que tu archivo sea un archivo excel valido (xls, xlsx)';
    vm.loading = false;
    $scope.$digest();
  }

  this.submitForm = function () {
    vm.formIsSubmit = true;

    $http.post('/api/upload/poblacion/' + $stateParams.id, {data: vm.data})
      .then(function (data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.poblaciones.index');
    }, function (resp) {
      console.log('Error status: ' + resp.status);
    });
  };

  function toJson(workbook) {
    var result = {};
    workbook.SheetNames.forEach(function(sheetName) {
      var roa = XLS.utils.sheet_to_row_object_array(workbook.Sheets[sheetName]);
      if(roa.length > 0){
        roa.index = 1;
        result[sheetName] = roa;
      }
    });
    return result;
  }

  function establecimientosNotFound() {
    return _.filter(vm.data, function(row) {
      return _.find(establecimientos, function (e) { return e.code == row.Codigo; }) == undefined;
    });
  }

};

module.exports = function (PoblacionService, $state, toastr) {
  'ngInject';
  var vm = this;
  vm.action = $state.current.data.title;
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.data = {
    name:  '',
    year: '',
  };
  vm.years = initYears();

  this.submitForm = function () {
    vm.formIsSubmit = true;

    PoblacionService.createResource(vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.poblaciones.import', {id: data.data.id}, {reload: true});
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

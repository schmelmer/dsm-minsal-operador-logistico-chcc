module.exports = function (data, PoblacionService, $state, toastr) {
  'ngInject';
  var vm = this;
  vm.action = $state.current.data.title;
  vm.data =  data;
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.years = initYears();

  this.submitForm = function () {
    vm.formIsSubmit = true;

    PoblacionService.updateResource(vm.data.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.poblaciones.index', {}, {reload: true});
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

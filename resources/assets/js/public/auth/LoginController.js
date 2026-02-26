module.exports = function ($auth, $http, $state, toastr, vcRecaptchaService) {
  'ngInject';
  var vm = this;
  vm.user = {};
  vm.errors = {};
  vm.response = null;
  vm.widgetId = null;

  vm.login = () => {
    var params = Object.assign({}, vm.user);
    params.recaptcha = vm.response;
    $auth.login(params)
      .then((response) => {
        toastr.success('Has iniciado sesión exitosamente!');
        $state.go('app.home');
      })
      .catch((response) => {
        vm.errors = response.data.errors;
        vcRecaptchaService.reload(vm.widgetId);
      });
  }

  vm.setResponse = function (response) {
    vm.response = response;
  };

  vm.setWidgetId = function (widgetId) {
    vm.widgetId = widgetId;
  };

  vm.cbExpiration = function() {
    vcRecaptchaService.reload(vm.widgetId);
    vm.response = null;
  };

  this.hasError = (property) => {
    return vm.errors.hasOwnProperty(property) ? true : false;
  };
};

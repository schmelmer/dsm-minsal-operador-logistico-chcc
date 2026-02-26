module.exports = function (estimaciones, puntosEntrega, producto, EstimacionManualService, $state, toastr, AclService) {
  'ngInject';
  var vm = this;
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.puntosEntrega = puntosEntrega;
  vm.producto = producto;
  vm.hasRole = AclService.hasRole;

  vm.estimaciones = puntosEntrega.map(function(p) {
    return {
      'punto': p.tipo,
      'punto_entrega_id': p.id,
      'producto_id': producto.id,
      'cajas_manuales': _.defaultTo(_.find(estimaciones, {'punto_entrega_id': p.id, 'producto_id': producto.id}), {cajas_manuales: 0}).cajas_manuales
    };
  });


  this.submitForm = function () {
    vm.formIsSubmit = true;

    // var productosModificados = vm.estimaciones.filter(estimacion => {
    //   return estimacion.cajas_manuales != null;
    // }).map(producto => {
    //   return {
    //     'punto_entrega_id': producto.punto_entrega_id,
    //     'producto_id': producto.producto_id,
    //     'cajas_manuales': producto.cajas_manuales
    //   };
    // });
    //
    // if (productosModificados.length == 0) {
    //   toastr.warning('No modificó ningun valor. Ingrese un dato numerico mayor a cero en alguna caja', 'Atención!');
    //   vm.formIsSubmit = false;
    //   return;
    // }

    EstimacionManualService.createOrUpdateResource(vm.estimaciones)
      .then(function(data) {
        toastr.success('Estimación modificada exitosamente', 'Estado!');
        $state.go('app.productos.index', {}, {reload: true});
      })
      .catch(function(errors) {
        vm.errors = errors.data;
      }).finally(function() {
        vm.formIsSubmit = false;
      });
  };

  this.hasError = function(property) {
    return vm.errors.hasOwnProperty(property);
  };

  this.classValidation = function(form, field) {
    return {
      'has-error': (field.$dirty || form.$submitted) && field.$invalid,
      'has-success': field.$dirty && field.$valid
    }
  };
};

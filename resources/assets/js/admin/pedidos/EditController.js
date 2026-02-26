module.exports = function (productos, puntoEntrega, puntosEntrega, pedido, estados, PedidoService, $state, toastr, AclService) {
  'ngInject';
  var vm = this;
  vm.action = 'Editar';
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.puntoEntrega = puntoEntrega;
  vm.puntosEntrega = puntosEntrega;
  vm.productos = productos;
  vm.estados = estados;
  vm.data = pedido;
  vm.hasRole = AclService.hasRole;
  console.log(vm.data);
  // vm.loadingEstimaciones = false;

  this.submitForm = function () {
    vm.formIsSubmit = true;

    vm.data.productos = _.map(vm.productos, function (producto) {
      return {'producto_id': producto.id, 'cantidad': producto.cantidad};
    });

    PedidoService.updateResource(pedido.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.pedidos.index', {}, {reload: true});
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      }).finally(function() {
        vm.formIsSubmit = false;
      });
  };

  this.cargaEstimaciones = function() {
    vm.loadingEstimaciones = true;
    EstimacionService.filterResources({puntoEntregaId: vm.data.puntoEntregaId}, false).then(function(data) {
      formatEstimaciones(data.data);
      vm.loadingEstimaciones = false;
    });
  }

  this.hasError = function(property) {
    return vm.errors.hasOwnProperty(property);
  };

  this.classValidation = function(form, field) {
    return {
      'has-error': (field.$dirty || form.$submitted) && field.$invalid,
      'has-success': field.$dirty && field.$valid
    }
  };


  function formatEstimaciones(estimaciones) {
    var keys = _.map(estimaciones, 'producto_id');
    vm.estimaciones = _.zipObject(keys, estimaciones);
    vm.cantidadPedidosProductos = calculaValoresPedidos();
  }

  function calculaValoresPedidos() {
    var valoresProductos = [];

    var pedidosFiltrados = _.filter(pedidos, function(p) {
      return p.punto_entrega_id == vm.data.puntoEntregaId;
    });

    _.each(_.map(pedidosFiltrados, 'detalle'), function (detalle) {
      _.each(detalle, function(detalleProducto) {
        var temp = valoresProductos[detalleProducto.producto_id] ? valoresProductos[detalleProducto.producto_id] : 0;
        valoresProductos[detalleProducto.producto_id] = temp + detalleProducto.cantidad;
      });
    });

    return valoresProductos;
  }

  vm.getCantidad = function (producto) {
    return (_.find(vm.data.detalle, function(d) {
      return d.producto_id == producto.id;
    }) || {cantidad: 0}).cantidad;
  };
};

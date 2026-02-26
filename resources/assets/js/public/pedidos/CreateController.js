module.exports = function (productos, puntosEntrega, estimaciones, pedidos, EstimacionService, PedidoService, $state, toastr, SweetAlert, $filter) {
  'ngInject';
  var vm = this;
  vm.action = 'Realizar pedido';
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.productos = productos;
  vm.data = {};
  vm.puntosEntrega = puntosEntrega;
  vm.loadingEstimaciones = false;
  init();

  this.confirmarPedido = function () {
    SweetAlert.swal({
      title: '<i class="fa fa-truck"></i> Confirmación de pedido',
      text: $('#pedido').html(),
      html: true,
      showCancelButton: true,
      confirmButtonColor: '#DD6B55',
      confirmButtonText: 'Confirmar',
      cancelButtonText: 'Cancelar',
      closeOnConfirm: true,
    }, function(isConfirm) {
      if (isConfirm) {
        vm.submitForm();
      }
    });
  }

  this.submitForm = function () {
    vm.formIsSubmit = true;

		var productos = $filter('filtroProductosDeServicio')(vm.productos, vm.puntoEntrega);
		productos = $filter('filtroProductosDeHospitales')(productos, vm.puntoEntrega);
    vm.data.productos = _.map(productos, function (producto) {
      return {'producto_id': producto.id, 'cantidad': producto.cantidad};
    });

    PedidoService.createResource(vm.data)
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
		vm.data.puntoEntregaId = vm.puntoEntrega.id;
    vm.loadingEstimaciones = true;
    EstimacionService.filterResources({puntoEntregaId: vm.data.puntoEntregaId}, false).then(function(data) {
      formatEstimaciones(data.data);
      vm.loadingEstimaciones = false;
    });
  }

  this.cajasDisponibles = function(productoId) {
    if (vm.estimaciones[productoId]) {
      var estimacionesCajas = vm.estimaciones[productoId].cajas_total;
      var pedidos = vm.cantidadPedidosProductos[productoId];
      var resto = estimacionesCajas - pedidos;
      if (pedidos == null) {
        return estimacionesCajas;
      }
      if (resto > 0) {
        return resto;
      }
    }
    return 0;
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

  this.calculaCajasPeriodo = function (producto) {
    if (typeof producto == 'undefined') {
      return 0;
    }
    var periodo = 12 / producto.puntoentrega.periodicidad;
    return Math.round(producto.cajas_total / periodo) || 0;
  }

  function init() {
    if (vm.puntosEntrega.length == 1) {
      vm.data.puntoEntregaId = vm.puntosEntrega[0].id;
			vm.puntoEntrega = vm.puntosEntrega[0];
    }
    formatEstimaciones(estimaciones);
  }

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
};

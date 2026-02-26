module.exports = function (agno, pedidos, productos, puntosEntrega, PedidoService) {
  'ngInject';
  var vm = this;
  vm.productos = productos;
  vm.pedidos = pedidos;
  vm.puntosEntrega = puntosEntrega;
  vm.loadingPedidos = false;
  vm.classEstados = {
    'solicitud_realizada': 'danger',
    'en_transito': 'warning',
    'en_punto_destino': 'success',
  };
  vm.columnaAcciones = _.some(vm.pedidos, {estado: 'en_punto_destino'});

  if (vm.puntosEntrega.length == 1) {
    vm.puntoEntregaId = vm.puntosEntrega[0].id;
  }

  vm.cargaPedidos = function() {
    vm.loadingPedidos = true;

    PedidoService.filterResources({agno: agno, puntoentrega: vm.puntoEntregaId}, false).then(function(data) {
      vm.pedidos = data.data;
      vm.loadingPedidos = false;
    });
  }

  vm.formatDate = function(date) {
    // safari no acepta '-' como fecha valida
    return new Date(date.replace(/-/g, "/"));
  };

  vm.productoNombreDetalle = function(detalle) {
    return _.find(vm.productos, function(p) {
      return p.id == detalle.producto_id;
    }).name;
  }

  vm.productoCajasDetalle = function(detalle) {
    var producto = _.find(vm.productos, function(p) {
      return p.id == detalle.producto_id;
    })
    return detalle.cantidad * producto.cajas;
  }
};

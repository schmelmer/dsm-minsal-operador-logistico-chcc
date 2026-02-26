module.exports = function (productos, pedidos, PedidoService, toastr, Confirm, $state, $stateParams, SweetAlert, AclService) {
  'ngInject';
  var vm = this;
  vm.productos = productos;
  vm.pedidos = pedidos.data;
  vm.totalItems = pedidos.total;
  vm.itemsPerPage = pedidos.per_page;
  vm.search = $stateParams;
  vm.page = pedidos.current_page;
  vm.reload = false;
  vm.classEstados = {
    'solicitud_realizada': 'danger',
    'en_transito': 'warning',
    'en_punto_destino': 'success',
  };
  vm.hasRole = AclService.hasRole;
  $(function () {
      $('[data-toggle="tooltip"]').tooltip();
  })

  vm.filter = function (page) {
    vm.search.page = page || 1;
    $state.go('.', vm.search, {reload: true});
    vm.reload = true;
  };

  vm.destroy = function (id, index) {
    Confirm.destroy(function() {
      vm.deletePedido(id, index);
    });
  };

  vm.deletePedido = function (id, index) {
    PedidoService.deleteResource(id)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        vm.removeFromPedido(index);
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      });
  };

  vm.removeFromPedido = function (index) {
    vm.pedidos.splice(index, 1);
  };

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

  vm.historial = function(index) {
    SweetAlert.swal({
				title: "Historial",
        text: $('#historial_' + index).html(),
				html: true
		});
  };

  vm.detalle = function(index) {
    SweetAlert.swal({
				title: "Detalle del pedido",
        text: $('#detalle_' + index).html(),
				html: true
		});
  };

  vm.observacionDistribuidor = function(pedido) {
    SweetAlert.swal({
				title: "Observación distribuidor",
        text: pedido.observacion_distribuidor
		});
  };
};

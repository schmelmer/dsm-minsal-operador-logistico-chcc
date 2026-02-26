module.exports = function (agno, productos, solicitudesRealizadas, solicitudesEnTransito, solicitudesEnPuntoDestino, estados, SweetAlert, NgMap, $http, PedidoService) {
  'ngInject';
  var vm = this;
  vm.productos = productos;
  vm.estados = estados;
  vm.solicitudesRealizadas = solicitudesRealizadas.data.filter(s => s.puntoentrega != null);
  vm.solicitudesEnTransito = solicitudesEnTransito.data.filter(s => s.puntoentrega != null);
  vm.solicitudesEnPuntoDestino = solicitudesEnPuntoDestino.data.filter(s => s.puntoentrega != null);

  vm.itemsPerPageRealizadas = solicitudesRealizadas.per_page;
  vm.totalItemsRealizadas = solicitudesRealizadas.total;
  vm.pageRealizadas = solicitudesRealizadas.current_page;

  vm.itemsPerPageEnTransito = solicitudesEnTransito.per_page;
  vm.totalItemsEnTransito = solicitudesEnTransito.total;
  vm.pageEnTransito = solicitudesEnTransito.current_page;

  vm.itemsPerPageEnPuntoDestino = solicitudesEnPuntoDestino.per_page;
  vm.totalItemsEnPuntoDestino = solicitudesEnPuntoDestino.total;
  vm.pageEnPuntoDestino = solicitudesEnPuntoDestino.current_page;
  vm.reload = false;

  vm.classEstados = {
    'solicitud_realizada': 'danger',
    'en_transito': 'warning',
    'en_punto_destino': 'success',
  };
  vm.data = {};
	vm.mapas = [];
	vm.pathAssets = Laravel.assetsImage;

  vm.filterRealizadas = function (page) {
    vm.reload = true;
    return PedidoService.filterResources({page: page, estado: 'solicitud_realizada'}).then(function(data) {
      vm.solicitudesRealizadas = data.data.data.filter(s => s.puntoentrega != null);
      vm.itemsPerPageRealizadas = data.data.per_page;
      vm.totalItemsRealizadas = data.data.total;
      vm.pageRealizadas = data.data.current_page;
      vm.reload = false;
    });
  };

  vm.filterEnTransito = function (page) {
    vm.reload = true;
    return PedidoService.filterResources({page: page, estado: 'en_transito'}).then(function(data) {
      vm.solicitudesEnTransito = data.data.data.filter(s => s.puntoentrega != null);
      vm.itemsPerPageEnTransito = data.data.per_page;
      vm.totalItemsEnTransito = data.data.total;
      vm.pageEnTransito = data.data.current_page;
      vm.reload = false;
    });
  };

  vm.filterEnPuntoDestino = function (page) {
    vm.reload = true;
    return PedidoService.filterResources({page: page, estado: 'en_punto_destino'}).then(function(data) {
      vm.solicitudesEnPuntoDestino = data.data.data.filter(s => s.puntoentrega != null);
      vm.itemsPerPageEnPuntoDestino = data.data.per_page;
      vm.totalItemsEnPuntoDestino = data.data.total;
      vm.pageEnPuntoDestino = data.data.current_page;
      vm.reload = false;
    });
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

  vm.actualizarEstado = function(index) {
    vm.data.estado = index;

    SweetAlert.swal({
      title: "Actualizar pedido",
      text: $('#edit-solicitud').html(),
      html: true
    });
  }

	vm.print = function(modalId, isActa) {
	  var name = isActa ? 'acta.pdf' : 'detalle.pdf';
	  var selector = isActa ? '#acta_' : '#myModal_';
		$http.post('/api/pdf', {'html': $(selector + modalId).find('.modal-dialog').html()}, {responseType: 'arraybuffer'})
			.then(function (data) {
				var file = new Blob([data.data], {type: 'application/pdf'});
				FileSaver.saveAs(file, name);
			}, function(error) {
				console.log({error});
			})
  }

	vm.today = function () {
		var d = new Date();
		var weekday=["Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"];
		var monthname=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
		return weekday[d.getDay()]+" "+d.getDate()+" de "+monthname[d.getMonth()]+" de "+d.getFullYear();
	}
};

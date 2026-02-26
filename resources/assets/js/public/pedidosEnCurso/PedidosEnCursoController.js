
module.exports = function (agno, productos, DTOptionsBuilder, DTColumnBuilder, DTDefaultOptions, PedidoService, $q) {
  'ngInject';
  var vm = this;
  vm.dtInstance = {};
  vm.dtOptions = DTOptionsBuilder
    .fromFnPromise(function() {
      var defer = $q.defer();
      PedidoService.pedidosEnCurso(agno).then(function(result) {
        defer.resolve(result.data);
      });
      return defer.promise;
    })
    .withButtons([
      {
        extend: 'excelHtml5',
        text: 'Exportar a excel',
        filename: 'Pedidos_en_curso_' + agno,
      }
    ])
    .withDisplayLength(100)
    .withDOM("<'dt-toolbar'<'col-xs-12 col-sm-6'f><'pull-right'B><'col-sm-6 col-xs-12 hidden-xs'>r>" +
      "t" + "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'>>p")
    .withBootstrap()
    .withOption('order', [3, 'desc']);

  vm.dtColumns = [
    DTColumnBuilder.newColumn('estado', 'Estado'),
    DTColumnBuilder.newColumn('fechaEntrega', 'Fecha entrega'),
    DTColumnBuilder.newColumn('observacionDistribuidor', 'Observación adicional'),
    DTColumnBuilder.newColumn('fechaSolicitud', 'Fecha Solicitud'),
    DTColumnBuilder.newColumn('servicio', 'Servicio de Salud'),
    DTColumnBuilder.newColumn('puntoEntrega', 'Punto de entrega'),
    DTColumnBuilder.newColumn('codigoTerritorio', 'Código que identifica el lugar para recibir los materiales'),
    DTColumnBuilder.newColumn('solicitanteNombre', 'Nombre de quien solicita'),
    DTColumnBuilder.newColumn('solicitanteEmail', 'Correo de contacto predefinido'),
    DTColumnBuilder.newColumn('solicitanteTelefono', 'Teléfono'),
  ];

  productos.forEach(function(producto) {
    vm.dtColumns.push(DTColumnBuilder.newColumn('productos.' + producto.id, producto.name).notSortable());
  });

  vm.dtColumns.push(
    DTColumnBuilder.newColumn('comuna', 'Comuna').notSortable(),
    DTColumnBuilder.newColumn('direccion', 'Dirección').notSortable(),
    DTColumnBuilder.newColumn('numero', 'Número').notSortable(),
    DTColumnBuilder.newColumn('folio', 'Folio').notSortable(),
    DTColumnBuilder.newColumn('observacionesCliente', 'Observaciones Cliente').notSortable(),
  );
};

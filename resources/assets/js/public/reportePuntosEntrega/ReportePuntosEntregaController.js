module.exports = function (agno, DTOptionsBuilder, DTColumnBuilder, DTDefaultOptions, ReportePuntosEntregaService, $q, DTRendererService, $scope) {
  'ngInject';
  var vm = this;
  vm.dtInstance = {};
  vm.tipo = 'Con Pedidos';
  vm.sinPedidos = '';
  vm.tipos = [
    {'id': '', 'value': 'Con pedidos'},
    {'id': 'sinPedidos', 'value': 'Sin pedidos'},
  ];
  initColumns();
  initOptions();

  vm.filter = function(sinPedido) {
    vm.sinPedidos =  sinPedido.id;
    vm.tipo = sinPedido.value;
    // initColumns();
    vm.dtInstance.rerender()
  }

  DTDefaultOptions.setLoadingTemplate('<div class="text-center"><i class="fa fa-refresh fa-spin fa-4x fa-fw"></i> <span class="sr-only">Cargando ...</span></div>');

  function initOptions() {
    vm.dtOptions = DTOptionsBuilder
      .fromFnPromise(function() {
        var defer = $q.defer();
        ReportePuntosEntregaService.get(agno, vm.sinPedidos).then(function(result) {
          defer.resolve(result.data);
        });
        return defer.promise;
      })
      .withDisplayLength(5000)
      .withDOM("<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'>r>" +
        "t" + "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'>>")
      .withBootstrap();
  }

  function initColumns() {
    // truco para mostrar el 'loading' una vez que se cambia las columnas
    DTRendererService.showLoading($('table'), $scope);

    vm.dtColumns = [
      DTColumnBuilder.newColumn('region', 'Region'),
      DTColumnBuilder.newColumn('servicio', 'Servicio'),
      DTColumnBuilder.newColumn('comuna', 'Comuna'),
      DTColumnBuilder.newColumn('establecimiento', 'Establecimiento'),
      DTColumnBuilder.newColumn('solicitante', 'Solicitante'),
      DTColumnBuilder.newColumn('correo', 'Correo'),
      DTColumnBuilder.newColumn('telefono', 'Telefono'),
      DTColumnBuilder.newColumn('direccion', 'Dirección'),
    ];
  }
};

module.exports = function (agno, agnos, stock, territorios, categorias, DTOptionsBuilder, DTColumnBuilder, DTDefaultOptions, ReporteStockService, $q, $timeout) {
  'ngInject';
  var vm = this;
  vm.stock = stock.data;
  vm.territorios = territorios;
  vm.territorios.unshift({name : "Pais",  type: "pais"});
  vm.tipo = 'Pais';
  vm.categoria = 'Todas';
  vm.producto = 'Todos';
  vm.estado = 'Activo';
  vm.agno = 'Todos';
  vm.productos = stock.productosFilter.slice(0);
  vm.productosFilter = stock.productosFilter.slice(0);
  vm.productosKeys = _.groupBy(vm.productosFilter, 'id');
  vm.search = {producto_id: []};
  vm.categorias = categorias;
  vm.categorias.unshift({id: '', name: 'Todas'})
  vm.productos.unshift({id: '', name: "Todos"});
  vm.estados = [
    {id: '', name: "Todos"},
    {id: '1', name: "Activo"},
    {id: '0', name: "No activo"},
  ];
  vm.agnos = agnos;
  vm.agnos.unshift('Todos');
  getTotales();

  vm.dtOptions = DTOptionsBuilder
    .newOptions()
    .withButtons([
      {extend: 'excelHtml5', text: 'Exportar a excel', filename: 'Reporte_stock_' + agno}
    ])
    .withDOM("<'dt-toolbar'<'col-xs-12 col-sm-6'f><'pull-right'B><'col-sm-6 col-xs-12 hidden-xs'>r>" +
      "t" + "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'>>p")
    .withBootstrap();

  var productosLength = vm.productos.length;
  vm.resumen = _.fill(Array(productosLength +  4), 0);

  vm.filter = function(territorio) {
    vm.tipo = territorio.name;
    vm.type =  territorio.type;
    fetchData();
  }

  vm.changeCategoria = function (categoria) {
    vm.categoria = categoria.name;
    vm.search.categoria_id = categoria.id;
    fetchData();
  }

  vm.changeProducto = function (producto) {
    vm.producto = producto.name;
    vm.search.producto_id = producto.id;
    fetchData();
  }

  vm.changeEstado = function (estado) {
    vm.estado = estado.name;
    vm.search.estado = estado.id;
    fetchData();
  }

  vm.changeAgno = function (agno) {
    vm.agno = agno
    fetchData();
  }

  function fetchData() {
    var params = {
      'producto_id': vm.search.producto_id,
      'categoria_id': vm.search.categoria_id,
      'estado': vm.search.estado,
    }
    ReporteStockService.get(vm.agno, vm.type, params).then(function(result) {
      vm.stock  = result.data.data;
      vm.productosFilter = result.data.productosFilter
      getTotales();
    });
  }

  // agrega totales a productosFilter...
  function getTotales() {
    let productosMap = _.flatten(_.map(vm.stock, 'productos'));
    _.each(vm.productosFilter, (producto) => {
      producto.totalAsignados = _.chain(productosMap).filter({'id': producto.id}).map('asignados').sum().value();
      producto.totalSolicitados = _.chain(productosMap).filter({'id': producto.id}).map('solicitados').sum().value();
      producto.totalDisponible = producto.totalAsignados - producto.totalSolicitados;
      producto.totalDisponibleStock = producto.totalDisponible * producto.cajas;
      // producto.totalPoblacion = _.chain(productosMap).filter({'id': producto.id}).map('poblacion').sum().value();
    });
  }

  function compute() {
    vm.resumen = _.fill(Array(productosLength +  4), 0);
    var displayedRows = vm.dtInstance.dataTable._('tr', {filter: 'applied'});
    angular.forEach(displayedRows, function(row) {

      productos.forEach(function(producto, index) {
        vm.resumen[index] += row.productos[producto.id];
      });

      var index = productosLength;
      vm.resumen[index] += row.cantidadSolicitudes;
      vm.resumen[index + 1] += row.enSolicitud;
      vm.resumen[index + 2] += row.enTransito;
      vm.resumen[index + 3] += row.enDestino;
    });

    vm.resumen.forEach(function(r, index) {
       $( vm.dtInstance.DataTable.column(index + 1).footer() ).html( r );
    });
  };

};

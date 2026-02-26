module.exports = function (ApiService, $http) {
  'ngInject';
  angular.extend(this, ApiService);
  this.resource = 'pedidos';

  this.rateResource = (id, data) => {
    return $http.put('/api/pedidosRate/'+ id, data, {ignoreLoadingBar: true});
  }

  this.makeObservation = (id, data) => {
    return $http.put('/api/pedidosObservation/'+ id, data, {ignoreLoadingBar: true});
  }

  this.pedidosEnCurso = (agno) => {
    return $http.get('/api/pedidosEnCurso/' + agno);
  }
};

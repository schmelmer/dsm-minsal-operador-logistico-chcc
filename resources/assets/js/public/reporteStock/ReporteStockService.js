module.exports = function (ApiService, $http) {
  'ngInject';

  this.get = (agno, tipo = '', params) => {
    return $http({method: 'get', 'url': '/api/reporteStock/'+ agno + '/' + tipo, params: params});
  }
};

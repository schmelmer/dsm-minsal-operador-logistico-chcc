module.exports = function ($http) {
  'ngInject';

  this.get = (agno, sinPedido = '') => {
    return $http.get('/api/reportePuntosEntrega/'+ agno + '/' + sinPedido);
  }
};

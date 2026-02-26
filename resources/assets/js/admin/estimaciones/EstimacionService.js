module.exports = function (ApiService, $http) {
  'ngInject';
  angular.extend(this, ApiService);
  this.resource = 'estimaciones';

	this.calculaEstimaciones = function() {
    return $http.get('/api/calculaEstimaciones');
	};

	this.calculaEstimacion = function(producto) {
    return $http.get('/api/calculaEstimacion/' + producto);
	};
};

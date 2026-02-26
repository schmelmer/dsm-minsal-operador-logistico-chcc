module.exports = function (ApiService, $http) {
  'ngInject';
  this.resource = 'comunas';

	this.distribuidorActual = function() {
    return $http.get('/api/distribuidorActual');
	};

	this.distribuidores = function(agno) {
    return $http.get('/api/distribuidores?agno=' + agno);
	};

	this.actualiza = function(data) {
    return $http.put('/api/distribuidor/', data, {ignoreLoadingBar: true});
	};
};

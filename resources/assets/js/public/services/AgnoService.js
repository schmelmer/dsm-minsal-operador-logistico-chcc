module.exports = function($http) {
	'ngInject';

	this.agnoActual = function() {
    return $http.get('/api/agnoActual');
	};

	this.agnosPedidos = function() {
    return $http.get('/api/agnosPedidos');
	};
}

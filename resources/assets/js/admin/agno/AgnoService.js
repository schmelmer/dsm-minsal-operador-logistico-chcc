module.exports = function($http) {
	'ngInject';

	this.agnoActual = function() {
    return $http.get('/api/agnoActual');
	};

	this.actualiza = function(data) {
    return $http.put('/api/agno/', data, {ignoreLoadingBar: true});
	};
}

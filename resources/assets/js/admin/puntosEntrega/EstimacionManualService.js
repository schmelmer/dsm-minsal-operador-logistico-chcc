module.exports = function ($http) {
  'ngInject';
  this.resource = 'estimacionesManuales';

	this.createOrUpdateResource = function(data) {
		return $http.post('/api/' + this.resource, data, {ignoreLoadingBar: true});
	};

	this.deServicios = function(productoId) {
    return $http.get('/api/' + this.resource + '/deServicios/' + productoId);
	}

	this.deHospitales = function(productoId) {
    return $http.get('/api/' + this.resource + '/deHospitales/' + productoId);
	}
};

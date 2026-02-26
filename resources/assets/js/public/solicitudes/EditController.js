module.exports = function (puntoEntrega, solicitud, estados, PedidoService, $state, toastr, SweetAlert, $auth, NgMap) {
  'ngInject';
  var vm = this;
  vm.action = 'Editar';
  vm.errors = {};
  vm.formIsSubmit = false;
  vm.puntoEntrega = puntoEntrega;
  vm.estados = estados;
  vm.data = solicitud;
	initPasos();
	initDropzone();


	function initPasos() {
		if (vm.data.estado == 'solicitud_realizada') {
			vm.paso = 1;
		} else if (vm.data.estado == 'en_transito') {
			vm.paso = 2;
		} else {
			vm.paso = 3;
		}
	}

	function initDropzone() {
		vm.dzMethods = {};
    var action = vm.paso == 3 ? 'Reemplazar' : 'Adjunte';
		vm.dzOptions = {
      thumbnailWidth: 250,//the "size of image" width at px
      thumbnailHeight: 250,//the "size of image" height at px
			params: {_method: 'PUT', estado: 'en_punto_destino'},
			headers: {Authorization: 'Bearer ' + $auth.getToken()},
			url: '/api/pedidos/' + solicitud.id,
			method: "post",
      dictDefaultMessage: '<i class="fa fa-paperclip"></i> ' + action + '  guia de despacho',
			dictRemoveFile: "Remover archivo",
			dictMaxFilesExceeded: "Solo puedes subir un solo archivo",
			dictCancelUpload: "Cancelar subida",
			dictCancelUploadConfirmation: "¿Esta seguro que desea cancelar la subida?",
			addRemoveLinks : true,
			autoProcessQueue:false,
			maxFiles: 1,
		};

		vm.dzCallbacks = {
			'success' : function(file, xhr){
        toastr.success('Subida de guia exitosa!', 'Estado!');
        $state.go('app.solicitudes.index', {}, {reload: true});
			},
			'error': function(errorMessage) {
        toastr.error('Un error ha ocurrido al intentar subir la guia. Intente nuevamente', 'Estado!');
			},
			'addedfile': function(file) {
				vm.file = file;
				var ext = file.name.split('.').pop();
				if (ext == "pdf") {
					$(file.previewElement).find(".dz-image img").attr("src", "/img/icons/pdf.png");
				} else if (ext.indexOf("doc") != -1) {
					$(file.previewElement).find(".dz-image img").attr("src", "/img/icons/word.png");
				} else if (ext.indexOf("xls") != -1) {
					$(file.previewElement).find(".dz-image img").attr("src", "/img/icons/excel.png");
				}
			}
		};
	}

	this.actualizaEstado = function (estado) {
		if (estado == 'solicitud_realizada') {
			SweetAlert.swal({
				title: 'Cambio de estado',
				text: 'Esta seguro de cambiar el pedido a solicitud realizada?',
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Confirmar',
				cancelButtonText: 'Cancelar',
				closeOnConfirm: true,
			}, function(isConfirm) {
				if (isConfirm) {
					vm.data.estado = 'solicitud_realizada';
					vm.submitForm();
				}
			});
    } else if (estado == 'en_transito' && vm.data.estado == 'solicitud_realizada') {
			SweetAlert.swal({
				title: 'Cambio de estado',
				text: 'Esta seguro de cambiar el pedido a en transito?',
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Confirmar',
				cancelButtonText: 'Cancelar',
				closeOnConfirm: true,
			}, function(isConfirm) {
				if (isConfirm) {
					vm.data.estado = 'en_transito';
					vm.submitForm();
				}
			});
    } else if (estado == 'en_punto_destino' && vm.paso >= 2) {
			if (!vm.file) {
				return SweetAlert.swal({
					title: 'Error',
					text: 'Adjunte guia de despacho',
					type: 'error',
					confirmButtonText: 'Aceptar',
					closeOnConfirm: true,
				});
			}
			SweetAlert.swal({
				title: 'Cambio de estado',
				text: '¿Esta seguro de cambiar el pedido a <strong> En punto de destino</strong>? <p>Comenzará la subida de la guia de despacho</p>',
        html: true,
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Confirmar',
				cancelButtonText: 'Cancelar',
				closeOnConfirm: true,
			}, function(isConfirm) {
				if (isConfirm) {
					vm.dzMethods.processQueue();
				}
			});
		}
	}

  this.submitForm = function () {
    vm.formIsSubmit = true;

    PedidoService.updateResource(solicitud.id, vm.data)
      .then(function(data) {
        toastr.success(data.data.message, 'Estado!');
        $state.go('app.solicitudes.index', {}, {reload: true});
      })
      .catch(function(errors) {
        vm.errors = errors.data.errors;
      }).finally(function() {
        vm.formIsSubmit = false;
      });
  };

  this.hasError = function(property) {
    return vm.errors.hasOwnProperty(property);
  };

  this.classValidation = function(form, field) {
    return {
      'has-error': (field.$dirty || form.$submitted) && field.$invalid,
      'has-success': field.$dirty && field.$valid
    }
  };

  var geocoder = new google.maps.Geocoder();
  var address = vm.puntoEntrega.direccion + ', ' + vm.puntoEntrega.region.name + ', Chile';
  geocoder.geocode({'address': address}, function(results, status){
    vm.showMap = status === google.maps.GeocoderStatus.OK && results.length > 0;
  });
};

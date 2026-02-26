module.exports = function OnConfig($stateProvider, $locationProvider, $urlRouterProvider) {
  'ngInject';

    /**
     * Helper auth functions
     */
  var skipIfLoggedIn = function($q, $auth, $location) {
    var deferred = $q.defer();
    if ($auth.isAuthenticated()) {
      $location.path('/home');
    } else {
      deferred.resolve();
    }
    return deferred.promise;
  };

  $stateProvider
  // auth
    .state('login', {
      url: '/login',
      controller: require('./auth/LoginController'),
      controllerAs: 'vm',
      template: require('./auth/login.html'),
      resolve: {
        skipIfLoggedIn: skipIfLoggedIn
      }
    })

    .state('logout', {
      url: '/logout',
      controller: require('./auth/LogoutController'),
      template: '<div></div>'
    })

    .state('register', {
      url: '/register',
      controller: require('./auth/RegisterController'),
      controllerAs: 'vm',
      template: require('./auth/register.html'),
      resolve: {
        skipIfLoggedIn: skipIfLoggedIn
      }
    })

  // application
    .state('app', {
      abstract: true,
      template: require('./layout/layout.html'),
      controller: function(user, AclService) {
        this.hasRole = AclService.hasRole;
        this.user = user;
        this.notificationsSize = _.size(user.notifications);
        this.unreadNotificationsSize = _.size(_.filter(user.notifications, function(n) {
          return ! n.read_at;
        }));
      },
      controllerAs: 'vm',
      resolve: {
        user: function(UserService, AclService, toastr, $auth, $state) {
          if (window.currentUser) {
            return window.currentUser;
          }
          // attach roles and user
          return UserService.me().then((data) => {
            _.flatMap(_.flatMap(data.data.roles), 'name').forEach(function(rol) {
              AclService.attachRole(rol);
            });
            window.currentUser = data.data;
            return data.data;
          }, (error) => {
            window.currentUser = null;
            $auth.removeToken();
            toastr.error(error.data.error, 'Estado!');
            $state.go('login');
          });
          return window.currentUser;
        },
        agno: function(AgnoService) {
          return AgnoService.agnoActual().then(function(data) {
            return data.data.agno
          });
        }
      }
    })

    .state('app.home', {
      url: '/home',
      controller: function($auth, data) {
        this.isAuthenticated = $auth.isAuthenticated;
        this.data = data;
        this.colors = ['blue', 'orange', 'green', 'blueDark', 'orangeDark', 'greenLight'];
        this.getColor = function(productName) {
          return this.colors[productName.length % this.colors.length ];
        }
      },
      controllerAs: 'vm',
      data: {
        title: 'Inicio'
      },
      template: require('./app/index.html'),
      resolve: {
        data: function($http) {
          return $http.get('/api/dashboard').then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.profile', {
      url: '/profile',
      controller: require('./profile/ProfileController'),
      controllerAs: 'vm',
      template: require('./profile/form.html'),
      data: {
        title: 'Perfil'
      }
    })

    .state('app.dashboard', {
      url: '/dashboard',
      template: require('./app/dashboard.html'),
      data: {
        title: 'dashboard'
      }
    })

  // pedidos - solicitudes
    .state('app.pedidos', {
      abstract: true,
      url: '/pedidos',
      template: '<ui-view></ui-view>',
      data: {title: 'Pedidos'},
      resolve: {
        acl: function(AclService, $q) {
          if (AclService.hasRole('distribuidor')) {
            return $q.reject('Unauthorized')
          }
        },
        puntosEntrega: function(PuntoEntregaService, user) {
          return PuntoEntregaService.filterResources({userId: user.id}, false).then(function(data) {
            return data.data;
          });
        },
        productos: function(ProductoService, agno) {
          return ProductoService.filterResources({year: agno}, false).then(function(data) {
            return data.data;
          });
        },
      }
    })

    .state('app.pedidos.index', {
      url: '/index',
      controller: require('./pedidos/ListController'),
      controllerAs: 'vm',
      template: require('./pedidos/views/index.html'),
      resolve: {
        pedidos: function(PedidoService, puntosEntrega, agno) {
          // casos para cuando usuario tiene mas de un punto de entrega o no tiene
          if (puntosEntrega.length > 1 || puntosEntrega.length == 0) {
            return [];
          }
          var puntosId = _.map(puntosEntrega, 'id')
          return PedidoService.filterResources({agno: agno, puntoentrega: puntosEntrega[0].id}, false).then(function(data) {
            return data.data;
          });
        },
      }
    })

    .state('app.pedidos.create', {
      url: '/create',
      controller: require('./pedidos/CreateController'),
      controllerAs: 'vm',
      template: require('./pedidos/views/form.html'),
      resolve: {
        estimaciones: function(EstimacionService, puntosEntrega) {
          // casos para cuando usuario tiene mas de un punto de entrega
          if (puntosEntrega.length > 1 || puntosEntrega.length == 0) {
            return;
          }

          return EstimacionService.filterResources({puntoEntregaId: puntosEntrega[0].id}, false).then(function(data) {
            return data.data;
          });
        },
        pedidos: function(PedidoService, puntosEntrega, agno) {

          if (puntosEntrega.length == 0) {
            return [];
          }

          var puntosId = _.map(puntosEntrega, 'id')
          return PedidoService.filterResources({agno: agno, puntosId: puntosId}, false).then(function(data) {
            return data.data;
          });
        },
        productos: function(ProductoService, agno) {
          return ProductoService.filterResources({year: agno, estado: 1}, false).then(function(data) {
            return data.data;
          });
        },
      }
    })

    .state('app.pedidos.edit', {
      url: '/edit/:id',
      controller: function() {},
      controllerAs: 'vm',
      template: require('./pedidos/views/form.html')
    })

    .state('app.pedidos.rate', {
      url: '/rate/:id',
      controller: require('./pedidos/RateController'),
      controllerAs: 'vm',
      template: require('./pedidos/views/rate.html'),
      resolve: {
        pedido: function(PedidoService, $stateParams) {
          return PedidoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        }
      }
    })

  // solicitudes
    .state('app.solicitudes', {
      abstract: true,
      url: '/solicitudes',
      template: '<ui-view/>',
      data: {title: 'Solicitudes'},
      resolve: {
        acl: function(AclService, $q, user) {
          if (!AclService.hasRole('distribuidor')) {
            return $q.reject('Unauthorized')
          }
        },
        productos: function(ProductoService, $stateParams) {
          return ProductoService.filterResources($stateParams, false).then(function(data) {
            return data.data;
          });
        },
        estados: function() {
          return [
            {'label': 'solicitud_realizada' , 'name':  'Solicitud realizada'},
            {'label': 'en_transito' , 'name':  'En tránsito'},
            {'label': 'en_punto_destino' , 'name':  'En punto de destino'},
          ];
        }
      }
    })

    .state('app.solicitudes.index', {
      url: '/index',
      controller: require('./solicitudes/ListController'),
      controllerAs: 'vm',
      template: require('./solicitudes/views/index.html'),
      resolve: {
				solicitudesRealizadas: function(PedidoService, $stateParams) {
          $stateParams.estado = 'solicitud_realizada';
          return PedidoService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
				},
				solicitudesEnTransito: function(PedidoService, $stateParams) {
          $stateParams.estado = 'en_transito';
          return PedidoService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
				},
				solicitudesEnPuntoDestino: function(PedidoService, $stateParams) {
          $stateParams.estado = 'en_punto_destino';
          return PedidoService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
				},
			}
    })

    .state('app.solicitudes.edit', {
      url: '/edit/:id',
      controller: require('./solicitudes/EditController'),
      controllerAs: 'vm',
      template: require('./solicitudes/views/form.html'),
      resolve: {
        solicitud: function(PedidoService, $stateParams) {
          return PedidoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        puntoEntrega: function(PuntoEntregaService, solicitud) {
          return PuntoEntregaService.getResource(solicitud.punto_entrega_id).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.solicitudes.observation', {
      url: '/observation/:id',
      controller: require('./solicitudes/ObservationController'),
      controllerAs: 'vm',
      template: require('./solicitudes/views/observation.html'),
      resolve: {
        solicitud: function(PedidoService, $stateParams) {
          return PedidoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.reporteStock', {
      url: '/reporteStock?tipo',
      data: {title: 'Reporte de Stock'},
      controller: require('./reporteStock/ReporteStockController'),
      controllerAs: 'vm',
      template: require('./reporteStock/views/index.html'),
      resolve: {
        territorios: function($http) {
          return $http.get('api/territorios').then(function (data) {
            return data.data;
          });
        },
        stock: function(ReporteStockService) {
          return ReporteStockService.get('Todos', '', {estado: '1'}).then(function(data) {
            return data.data;
          });
        },
        categorias: function(CategoriaService) {
          return CategoriaService.getResources().then(function(data) {
            return data.data;
          });
        },
        agnos: function(AgnoService) {
          return AgnoService.agnosPedidos().then(function(data) {
            return data.data;
          });
        }
      }
    })


    .state('app.reportePuntosEntrega', {
      url: '/reportePuntosEntrega?sinPedidos',
      data: {title: 'Puntos de entrega'},
      controller: require('./reportePuntosEntrega/ReportePuntosEntregaController'),
      controllerAs: 'vm',
      template: require('./reportePuntosEntrega/views/index.html'),
    })

    .state('app.pedidosEnCurso', {
      url: '/pedidosEnCurso?agno',
      data: {title: 'Pedidos en curso'},
      controller: require('./pedidosEnCurso/PedidosEnCursoController'),
      controllerAs: 'vm',
      template: require('./pedidosEnCurso/views/index.html'),
      resolve: {
        productos: function(ProductoService, agno) {
          return ProductoService.filterResources({year: agno}, false).then(function(data) {
            return data.data;
          });
        }
      }
    })

  $urlRouterProvider.otherwise('/login');
};

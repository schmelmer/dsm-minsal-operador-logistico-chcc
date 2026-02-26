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

  // application
    .state('app', {
      abstract: true,
      template: require('./layout/layout.html'),
      controller: function(user, AclService) {
        this.user = user;
        this.hasRole = AclService.hasRole;
      },
      controllerAs: 'vm',
      resolve: {
        user: function(UserService, AclService, $auth, toastr, $state) {
          return UserService.me().then((data) => {
            _.flatMap(_.flatMap(data.data.roles), 'name').forEach(function(rol) {
              AclService.attachRole(rol);
            });
            return data.data;
          }, (error) => {
            $auth.removeToken();
            toastr.error(error.data.error, 'Estado!');
            $state.go('login');
          });
        }
      },
    })

    .state('app.home', {
      url: '/home',
      controller: function($auth) { this.isAuthenticated = $auth.isAuthenticated },
      controllerAs: 'vm',
      template: require('./app/index.html')
    })

    .state('app.users', {
      abstract: true,
      url: '/users',
      template: '<ui-view/>',
      data: {title: 'Usuarios'}
    })

    .state('app.users.index', {
      url: '?page&name&email&roles&admin&servicio&comuna&establecimiento',
      data: {title: 'Listado'},
      controller: require('./users/ListController'),
      controllerAs: 'vm',
      template: require('./users/views/index.html'),
      resolve: {
        users: function(UserService, $stateParams) {
          $stateParams.admin = 1
          return UserService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.users.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./users/CreateController'),
      controllerAs: 'vm',
      template: require('./users/views/form.html'),
      resolve : {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        roles: function(RoleService) {
          return RoleService.getResources().then(function(data) {
            return data.data.data;
          });
        }
      }
    })

    .state('app.users.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./users/EditController'),
      controllerAs: 'vm',
      template: require('./users/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(UserService, $stateParams) {
          return UserService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        roles: function(RoleService) {
          return RoleService.getResources().then(function(data) {
            return data.data.data;
          });
        }
      },
    })

    .state('app.users.show', {
      url: '/show/:id',
      data: {title: 'Ver'},
      controller: require('./users/ShowController'),
      controllerAs: 'vm',
      template: require('./users/views/show.html'),
      resolve: {
        data: function(UserService, $stateParams) {
          return UserService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        roles: function(RoleService) {
          return RoleService.getResources().then(function(data) {
            return data.data.data;
          });
        }
      },
    })

    .state('app.roles', {
      abstract: true,
      url: '/roles',
      template: '<ui-view/>',
      data: {title: 'Roles'}
    })

    .state('app.roles.index', {
      url: '?page&name&label&permissions',
      data: {title: 'Listado'},
      controller: require('./roles/ListController'),
      controllerAs: 'vm',
      template: require('./roles/views/index.html'),
      resolve: {
        roles: function(RoleService, $stateParams) {
          return RoleService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.roles.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./roles/CreateController'),
      controllerAs: 'vm',
      template: require('./roles/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        permissions: function(PermissionService) {
          return PermissionService.all().then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.roles.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controllerAs: 'vm',
      controller: require('./roles/EditController'),
      template: require('./roles/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        role: function(RoleService, $stateParams) {
          return RoleService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        permissions: function(PermissionService) {
          return PermissionService.all().then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.regions', {
      abstract: true,
      url: '/regiones',
      template: '<ui-view/>',
      data: {title: 'Regiones'}
    })

    .state('app.regions.index', {
      url: '?page&name&code',
      data: {title: 'Listado'},
      controller: require('./regions/ListController'),
      controllerAs: 'vm',
      template: require('./regions/views/index.html'),
      resolve: {
        regions: function(RegionService, $stateParams) {
          return RegionService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.regions.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./regions/CreateController'),
      controllerAs: 'vm',
      template: require('./regions/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        }
      }
    })

    .state('app.regions.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./regions/EditController'),
      controllerAs: 'vm',
      template: require('./regions/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(RegionService, $stateParams) {
          return RegionService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        }
      },
    })

    .state('app.serviciosSalud', {
      abstract: true,
      url: '/serviciosSalud',
      template: '<ui-view/>',
      data: {title: 'Servicios de Salud'}
    })

    .state('app.serviciosSalud.index', {
      url: '?page&name&code&region',
      data: {title: 'Listado'},
      controller: require('./serviciosSalud/ListController'),
      controllerAs: 'vm',
      template: require('./serviciosSalud/views/index.html'),
      resolve: {
        serviciosSalud: function(ServicioSaludService, $stateParams) {
          return ServicioSaludService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.serviciosSalud.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./serviciosSalud/CreateController'),
      controllerAs: 'vm',
      template: require('./serviciosSalud/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        regions: function(RegionService) {
          return RegionService.getResources().then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.serviciosSalud.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./serviciosSalud/EditController'),
      controllerAs: 'vm',
      template: require('./serviciosSalud/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(ServicioSaludService, $stateParams) {
          return ServicioSaludService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        regions: function(RegionService) {
          return RegionService.getResources().then(function(data) {
            return data.data;
          });
        }
      },
    })

    .state('app.comunas', {
      abstract: true,
      url: '/comunas',
      template: '<ui-view/>',
      data: {title: 'Comunas'}
    })

    .state('app.comunas.index', {
      url: '?page&name&code&servicio',
      data: {title: 'Listado'},
      controller: require('./comunas/ListController'),
      controllerAs: 'vm',
      template: require('./comunas/views/index.html'),
      resolve: {
        comunas: function(ComunaService, $stateParams) {
          return ComunaService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.comunas.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./comunas/CreateController'),
      controllerAs: 'vm',
      template: require('./comunas/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        servicios: function(ServicioSaludService) {
          return ServicioSaludService.getResources().then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.comunas.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./comunas/EditController'),
      controllerAs: 'vm',
      template: require('./comunas/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(ComunaService, $stateParams) {
          return ComunaService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        servicios: function(ServicioSaludService) {
          return ServicioSaludService.getResources().then(function(data) {
            return data.data;
          });
        }
      },
    })

    .state('app.establecimientos', {
      abstract: true,
      url: '/establecimientos',
      template: '<ui-view/>',
      data: {title: 'Establecimientos'}
    })

    .state('app.establecimientos.index', {
      url: '?page&name&code&servicio&hospital&es_nivel_secundario',
      data: {title: 'Listado'},
      controller: require('./establecimientos/ListController'),
      controllerAs: 'vm',
      template: require('./establecimientos/views/index.html'),
      resolve: {
        establecimientos: function(EstablecimientoService, $stateParams) {
          return EstablecimientoService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.establecimientos.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./establecimientos/CreateController'),
      controllerAs: 'vm',
      template: require('./establecimientos/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        comunas: function(ComunaService) {
          return ComunaService.getResources().then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.establecimientos.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./establecimientos/EditController'),
      controllerAs: 'vm',
      template: require('./establecimientos/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(EstablecimientoService, $stateParams) {
          return EstablecimientoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        comunas: function(ComunaService) {
          return ComunaService.getResources().then(function(data) {
            return data.data;
          });
        }
      },
    })

    .state('app.puntosEntrega', {
      abstract: true,
      url: '/puntosEntrega',
      template: '<ui-view/>',
      data: {title: 'Puntos de entrega'},
      resolve: {
        territorios: function($http) {
          return $http.get('/api/territorios').then(function (data) {
            return data.data;
          });
        }
      }
    })

    .state('app.puntosEntrega.index', {
      url: '?page&calle&numero&region&servicio&comuna&establecimiento&responsable&tipo',
      data: {title: 'Listado'},
      controller: require('./puntosEntrega/ListController'),
      controllerAs: 'vm',
      template: require('./puntosEntrega/views/index.html'),
      resolve: {
        puntosEntrega: function(PuntoEntregaService, $stateParams) {
          return PuntoEntregaService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.puntosEntrega.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./puntosEntrega/CreateController'),
      controllerAs: 'vm',
      template: require('./puntosEntrega/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        regiones: function(RegionService) {
          return RegionService.getResources().then(function(data) {
            return data.data;
          });
        },
        responsables: function(UserService) {
          return UserService.getResources().then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.puntosEntrega.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./puntosEntrega/EditController'),
      controllerAs: 'vm',
      template: require('./puntosEntrega/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        regiones: function(RegionService) {
          return RegionService.getResources().then(function(data) {
            return data.data;
          });
        },
        responsables: function(UserService) {
          return UserService.getResources().then(function(data) {
            return data.data;
          });
        },
        data: function(PuntoEntregaService, $stateParams) {
          return PuntoEntregaService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        region: function(RegionService, data) {
          return RegionService.getResource(data.region_id, 'servicios.comunas.establecimientos').then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.puntosEntrega.estimaciones', {
      url: '/estimaciones/:id',
      data: {title: 'Estimaciones'},
      controller: require('./puntosEntrega/EstimacionesController'),
      controllerAs: 'vm',
      template: require('./puntosEntrega/views/estimaciones.html'),
      resolve: {
        agno: function(AgnoService) {
          return AgnoService.agnoActual().then(function(data) {
            return data.data.agno
          });
        },
        estimaciones: function(EstimacionService, $stateParams) {
          return EstimacionService.filterResources({puntoEntregaId: $stateParams.id}, false).then(function(data) {
            return data.data;
          });
        },
      }
    })

    .state('app.categorias', {
      abstract: true,
      url: '/categorias',
      data: {title: 'Categorias'},
      template: '<ui-view/>'
    })

    .state('app.categorias.index', {
      url: '?page&name',
      data: {title: 'Listado'},
      controller: require('./categorias/ListController'),
      controllerAs: 'vm',
      template: require('./categorias/views/index.html'),
      resolve: {
        categorias: function(CategoriaService, $stateParams) {
          return CategoriaService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.categorias.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./categorias/CreateController'),
      controllerAs: 'vm',
      template: require('./categorias/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        }
      }
    })

    .state('app.categorias.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./categorias/EditController'),
      controllerAs: 'vm',
      template: require('./categorias/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(CategoriaService, $stateParams) {
          return CategoriaService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        }
      },
    })

    .state('app.poblaciones', {
      abstract: true,
      url: '/poblaciones',
      data: {title: 'Poblaciones'},
      template: '<ui-view/>',
    })

    .state('app.poblaciones.index', {
      url: '?page&name&year',
      data: {title: 'Listado'},
      controller: require('./poblaciones/ListController'),
      controllerAs: 'vm',
      template: require('./poblaciones/views/index.html'),
      resolve: {
        poblaciones: function(PoblacionService, $stateParams) {
          return PoblacionService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.poblaciones.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./poblaciones/CreateController'),
      controllerAs: 'vm',
      template: require('./poblaciones/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        }
      }
    })

    .state('app.poblaciones.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./poblaciones/EditController'),
      controllerAs: 'vm',
      template: require('./poblaciones/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(PoblacionService, $stateParams) {
          return PoblacionService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.poblaciones.import', {
      url: '/:id/import',
      data: {title: 'Importar'},
      controller: require('./poblaciones/ImportController'),
      controllerAs: 'vm',
      template: require('./poblaciones/views/import.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        establecimientos: function(EstablecimientoService, $stateParams) {
          return EstablecimientoService.getResources('code,name').then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.productos', {
      abstract: true,
      url: '/productos',
      template: '<ui-view/>',
      data: {title: 'Productos'}
    })

    .state('app.productos.index', {
      url: '?page&name&stock&categoria&poblacion&year&seremi&hospitales&estado',
      data: {title: 'Listado'},
      controller: require('./productos/ListController'),
      controllerAs: 'vm',
      template: require('./productos/views/index.html'),
      resolve: {
        productos: function(ProductoService, $stateParams) {
          return ProductoService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.productos.create', {
      url: '/create',
      data: {title: 'Crear'},
      controller: require('./productos/CreateController'),
      controllerAs: 'vm',
      template: require('./productos/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        categorias: function(CategoriaService) {
          return CategoriaService.getResources().then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.productos.edit', {
      url: '/edit/:id',
      data: {title: 'Editar'},
      controller: require('./productos/EditController'),
      controllerAs: 'vm',
      template: require('./productos/views/form.html'),
      resolve: {
        acl: function(AclService, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        data: function(ProductoService, $stateParams) {
          return ProductoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        categorias: function(CategoriaService) {
          return CategoriaService.getResources().then(function(data) {
            return data.data;
          });
        },
        poblaciones: function(PoblacionService, data) {
          return PoblacionService.filterResources({year: data.year}, false).then(function(data) {
            return data.data;
          });
        }
      },
    })

    .state('app.productos.deServicios', {
      url: '/deServicios/:id',
      data: {title: 'Estimaciones'},
      controller: require('./productos/deServiciosController'),
      controllerAs: 'vm',
      template: require('./productos/views/deServicios.html'),
      resolve: {
        agno: function(AgnoService) {
          return AgnoService.agnoActual().then(function(data) {
            return data.data.agno
          });
        },
        estimaciones: function(EstimacionManualService, $stateParams) {
          return EstimacionManualService.deServicios($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        producto: function(ProductoService, $stateParams) {
          return ProductoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        puntosEntrega: function(PuntoEntregaService) {
          return PuntoEntregaService.filterResources({tipo: 'servicio,region'}, false).then(function(data) {
            return data.data;
          });
        }
      }
    })

    .state('app.productos.deHospitales', {
      url: '/deHospitales/:id',
      data: {title: 'Estimaciones'},
      controller: require('./productos/deHospitalesController'),
      controllerAs: 'vm',
      template: require('./productos/views/deHospitales.html'),
      resolve: {
        agno: function(AgnoService) {
          return AgnoService.agnoActual().then(function(data) {
            return data.data.agno
          });
        },
        estimaciones: function(EstimacionManualService, $stateParams) {
          return EstimacionManualService.deHospitales($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        producto: function(ProductoService, $stateParams) {
          return ProductoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        puntosEntrega: function(PuntoEntregaService) {
          return PuntoEntregaService.filterResources({hospital: true}, false).then(function(data) {
            return data.data;
          });
        }
      }
    })

    // pedidos - solicitudes
    .state('app.pedidos', {
      abstract: true,
      url: '/pedidos',
      template: '<ui-view/>',
      data: {title: 'Pedidos'},
      resolve: {
        agno: function(AgnoService) {
          return AgnoService.agnoActual().then(function(data) {
            return data.data.agno
          });
        },
        productos: function(ProductoService, agno, $stateParams) {
          // return ProductoService.filterResources({year: agno}, false).then(function(data) {
          return ProductoService.getResources().then(function(data) {
            return data.data;
          });
        },
      }
    })

    .state('app.pedidos.index', {
      url: '/index?page&id&servicio&comuna&establecimiento&estado&evaluacion&punto&agno',
      controller: require('./pedidos/ListController'),
      controllerAs: 'vm',
      template: require('./pedidos/views/index.html'),
      resolve: {
        pedidos: function(PedidoService, agno, $stateParams) {
          // $stateParams.agno = agno;
          return PedidoService.filterResources($stateParams).then(function(data) {
            return data.data;
          });
        },
      }
    })

    .state('app.pedidos.edit', {
      url: '/edit/:id',
      controller: require('./pedidos/EditController'),
      controllerAs: 'vm',
      template: require('./pedidos/views/form.html'),
      resolve: {
        acl: function(AclService, user, $q) {
          if (!AclService.hasRole('super-admin')) {
            return $q.reject('Unauthorized')
          }
        },
        pedido: function(PedidoService, $stateParams) {
          return PedidoService.getResource($stateParams.id).then(function(data) {
            return data.data;
          });
        },
        puntoEntrega: function(PuntoEntregaService, pedido) {
          return PuntoEntregaService.getResource(pedido.punto_entrega_id).then(function(data) {
            return data.data;
          }).catch(function(error) {
            console.log('error', error);
          });
        },
        puntosEntrega: function(PuntoEntregaService, $stateParams) {
          return PuntoEntregaService.filterResources({}, false).then(function(data) {
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

    .state('app.configuracion', {
      url: '/configuracion',
      controller: require('./configuracion/ListController'),
      controllerAs: 'vm',
      template: require('./configuracion/views/index.html'),
      data: {title: 'Configuración del sistema'},
      resolve: {
        agno: function(AgnoService) {
          return AgnoService.agnoActual().then(function(data) {
            return data.data.agno
          });
        },
        agnos: function() {
          var beginYear = 2016;
          var currentYear = new Date().getFullYear();
          var lastYear = currentYear + 10;
          var years = [];
          for (var i = beginYear; i < lastYear; i++){
            years.push(i);
          }
          return years;
        },
        distribuidor: function(ConfiguracionService) {
          return ConfiguracionService.distribuidorActual().then(function(data) {
            return data.data.distribuidor;
          });
        },
        distribuidores: function(ConfiguracionService, agno) {
          return ConfiguracionService.distribuidores(agno).then(function(data) {
            return data.data;
          });
        },
      }
    })

    .state('root', {
      url: '/',
      external: true
    });

  $urlRouterProvider.otherwise('/login');
};

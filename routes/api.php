<?php

use Illuminate\Http\Request;

Route::group(['middleware' => ['auth:api', 'admin'], 'namespace' => 'Api'], function () {
    Route::resource('users', 'UsersController');
    Route::resource('roles', 'RolesController');
    Route::get('permissions', 'PermissionsController@index');
    Route::resource('regions', 'RegionController');
    Route::resource('serviciosSalud', 'ServicioSaludController');
    Route::resource('comunas', 'ComunaController');
    Route::resource('establecimientos', 'EstablecimientoController');
    Route::resource('poblaciones', 'PoblacionController', ['parameters' => [
        'poblaciones' => 'poblacion'
    ]]);
    Route::post('upload/poblacion/{poblacion}', 'PoblacionUploadController@upload');
    Route::get('/admin/me', function (Request $request) {
        return $request->user()->load('notifications', 'roles:id,name');
    });
    Route::get('/calculaEstimaciones', function (\App\Services\CalculaEstimacion $estimacion) {
        $estimacion->calculaProductos();
        return response()->json(['message' => 'Estimaciones finalizadas!']);
    });
    Route::get('/calculaEstimacion/{producto}', function (\App\Services\CalculaEstimacion $estimacion, Producto $producto) {
        $estimacion->calcula($producto);
        return response()->json(['message' => 'Estimación finalizada!']);
    });
});
Route::resource('categorias', 'Api\CategoriaController');

Route::group(['middleware' => 'auth:api', 'namespace' => 'Api'], function () {
    Route::get('/me', function (Request $request) {
        return $request->user()->load('notifications', 'roles:id,name');
    });
    Route::post('/markNotificationsAsRead', function (Request $request) {
        $request->user()->unreadNotifications()->update(['read_at' => \Carbon\Carbon::now()]);
    });
    Route::resource('estimaciones', 'EstimacionController', ['parameters' => [
        'estimaciones' => 'estimacion'
    ]]);
    Route::resource('estimacionesManuales', 'EstimacionManualController', ['parameters' => [
        'estimacionesManuales' => 'estimacion'
    ]]);
    Route::get('estimacionesManuales/deServicios/{productoId}', 'EstimacionManualController@indexDeServicios');
    Route::get('estimacionesManuales/deHospitales/{productoId}', 'EstimacionManualController@indexDeHospitales');
    Route::get('dashboard', 'DashboardController@index');
    Route::get('territorios', 'TerritorioController@index');
    Route::resource('puntosEntrega', 'PuntoEntregaController');
    Route::resource('productos', 'ProductoController');
    Route::resource('pedidos', 'PedidoController');
    Route::put('pedidosRate/{pedido}', 'PedidoController@rate');
    Route::put('pedidosObservation/{pedido}', 'PedidoController@makeObservation');
    Route::post('changePassword', 'UsersController@changePassword');
    Route::get('reporteSolicitudes/{agno}/{tipo?}', 'ReporteSolicitudesController@index');
    Route::get('reporteStock/{agno}/{tipo?}', 'ReporteStockController@index');
    Route::get('reportePuntosEntrega/{agno}/{sinPedidos?}', 'ReportePuntosEntregaController@index');
    Route::get('pedidosEnCurso/{agno}', 'PedidosEnCursoController@index');
});

// configuracion agno
Route::get('agnoActual', function () {
    $agnoActual = \App\Configuracion::first()->agno;

    return response()->json(['agno' => $agnoActual]);
});

Route::put('agno', function (Request $request) {
    $agno = $request->agno;

    \App\Configuracion::first()->fill(['agno' => $agno])->save();

    return response()->json(['message' => 'Año actualizado correctamente']);
});

Route::get('agnosPedidos', function () {
    $agnos = DB::table('pedidos')->select('agno')->distinct()->get()->pluck('agno');

    return response()->json($agnos);
});


// configuracion distribuidor
Route::get('distribuidorActual', function () {
    $distribuidorActual = \App\Configuracion::first()->distribuidor;

    return response()->json(['distribuidor' => $distribuidorActual]);
});

Route::put('distribuidor', function (Request $request) {
    $distribuidor = $request->distribuidor;

    \App\Configuracion::first()->fill(['distribuidor' => $distribuidor])->save();

    return response()->json(['message' => 'Distribuidor actualizado correctamente']);
});


Route::get('distribuidores', function () {
    $distribuidores = \App\User::whereHas('roles', function ($r) {
        return $r->whereName('distribuidor');
    })->get();

    return response()->json($distribuidores);
});


Route::post('/pdf', function (Request $request) {
    return PDF::loadView('pdf', ['html' => $request->html])->inline();
});

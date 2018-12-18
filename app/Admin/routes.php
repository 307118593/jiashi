<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    // 'middleware'=>'admin.permission:allow,administrator'
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('banner',BannerController::class);
    $router->resource('user',UserController::class);
    $router->resource('camera',CameraController::class);
    $router->resource('camera_auth',Camera_authController::class);
    $router->resource('camera_log',Camera_logController::class);
    $router->resource('project', ProjectController::class);
    $router->resource('staff', StaffController::class);
    $router->resource('flow', FlowController::class);
    $router->resource('flow_model', Flow_modelController::class);
    $router->resource('broadcast', BroadcastController::class);
    $router->resource('customer', CustomerController::class);
    $router->resource('cases', CasesController::class);
    $router->resource('share', ShareController::class);
    $router->resource('pics', PicsController::class);
    $router->resource('messages', MessagesController::class);
    $router->resource('welcome', WelcomeController::class);
    $router->resource('activitys', ActivitysController::class);
    $router->resource('residence', ResidenceController::class);
    $router->resource('build_case', Build_caseController::class);
    $router->resource('record', RecordController::class);
    $router->resource('arts', ArtsController::class);
    $router->get('ad', 'AdController@index');
    $router->post('setCompany', 'AdController@setCompany');
    $router->get('api/users', 'Camera_authController@users');
});

// Route::group([
//     'prefix'        => config('admin.route.prefix'),
//     'namespace'     => config('admin.route.namespace'),
//     'middleware'    => config('admin.route.middleware'),
//     // 'middleware'=>'admin.permission:allow,zxgs'
// ], function (Router $router) {

//     $router->get('/', 'HomeController@index');
//     $router->resource('project', ProjectController::class);
// });

<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/auth'], function () use ($router) {
    $router->post('login', ['uses' => 'AuthController@login']);
    $router->delete('logout', ['uses' => 'AuthController@logout']);
    $router->post('register', ['uses' => 'AuthController@register']);
    $router->get('me', ['uses' => 'AuthController@me']);
    $router->post('refresh', ['uses' => 'AuthController@refresh']);
});
// Api for User
$router->group(['prefix' => 'api'], function () use ($router) {

    $router->get('user',  ['uses' => 'UserController@index']);

    $router->get('user/search', ['uses' => 'UserController@search']);

    $router->get('user/{id}', ['uses' => 'UserController@show']);

    $router->post('user', ['uses' => 'UserController@store']);

    $router->put('user/{id}', ['uses' => 'UserController@update']);

    $router->delete('user/{id}', ['uses' => 'UserController@delete']);
});

Route::get('/verify/{code}', 'AuthController@verify');
Route::get('/resend-email/{id}', 'AuthController@resendEmail');
// Api for Team
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('team',  ['uses' => 'TeamController@index']);

    $router->get('team/search', ['uses' => 'TeamController@search']);

    $router->get('team/{id}',  ['uses' => 'TeamController@show']);

    $router->post('team',  ['uses' => 'TeamController@store']);

    $router->put('team/{id}', ['uses' => 'TeamController@update']);

    $router->delete('team/{id}', ['uses' => 'TeamController@delete']);
});


// Api for UserTeam
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('userteam',  ['uses' => 'UserTeamController@index']);

    $router->get('userteam/{id}',  ['uses' => 'UserTeamController@show']);

    $router->post('userteam',  ['uses' => 'UserTeamController@store']);

    $router->put('userteam/{id}', ['uses' => 'UserTeamController@update']);

    $router->delete('userteam/{id}', ['uses' => 'UserTeamController@delete']);
});


// Api for Role
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('role',  ['uses' => 'RoleController@index']);

    $router->get('role/search', ['uses' => 'RoleController@search']);

    $router->get('role/{id}',  ['uses' => 'RoleController@show']);

    $router->post('role',  ['uses' => 'RoleController@store']);

    $router->put('role/{id}',  ['uses' => 'RoleController@update']);

    $router->delete('role/{id}',  ['uses' => 'RoleController@delete']);
});

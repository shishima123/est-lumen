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

Route::get('/verify/{code}', 'AuthController@verify');
Route::get('/resend-email/{id}', 'AuthController@resendEmail');

$router->group(['prefix' => 'api'], function () use ($router) {

    // Api for User
    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('index',  ['uses' => 'UserController@index']);

        $router->get('show/{id}', ['uses' => 'UserController@show']);

        $router->post('store', ['uses' => 'UserController@store']);

        $router->put('update/{id}', ['uses' => 'UserController@update']);

        $router->delete('delete/{id}', ['uses' => 'UserController@delete']);
    });


    // Api for Team
    $router->group(['prefix' => 'team'], function () use ($router) {
        $router->get('index',  ['uses' => 'TeamController@index']);

        $router->get('show/{id}',  ['uses' => 'TeamController@show']);

        $router->post('store',  ['uses' => 'TeamController@store']);

        $router->put('update/{id}', ['uses' => 'TeamController@update']);

        $router->delete('delete/{id}', ['uses' => 'TeamController@delete']);
    });

    // Api for UserTeam
    $router->group(['prefix' => 'userteam'], function () use ($router) {
        $router->get('index',  ['uses' => 'UserTeamController@index']);

        $router->get('show/{id}',  ['uses' => 'UserTeamController@show']);

        $router->post('store',  ['uses' => 'UserTeamController@store']);

        $router->delete('/remove-user', ['uses' => 'UserTeamController@removeUserInTeam']);

        $router->post('change-admin', ['uses' => 'UserTeamController@changeAdmin']);
    });


    // Api for Role
    $router->group(['prefix' => 'role'], function () use ($router) {
        $router->get('index',  ['uses' => 'RoleController@index']);

        $router->get('show/{id}',  ['uses' => 'RoleController@show']);

        $router->post('store',  ['uses' => 'RoleController@store']);

        $router->put('update/{id}',  ['uses' => 'RoleController@update']);

        $router->delete('delete/{id}',  ['uses' => 'RoleController@delete']);
    });
});

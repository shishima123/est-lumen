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

// Api for User
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('user',  ['uses' => 'UserController@index']);

    $router->get('user/{id}', ['uses' => 'UserController@show']);

    $router->post('user', ['uses' => 'UserController@store']);

    $router->delete('user/{id}', ['uses' => 'UserController@delete']);

    $router->put('user/{id}', ['uses' => 'UserController@update']);
});


// Api for Team
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('team',  ['uses' => 'TeamController@index']);

    $router->get('team/{id}',  ['uses' => 'TeamController@show']);

    $router->post('team',  ['uses' => 'TeamController@store']);

    $router->put('team/{id}', ['uses' => 'TeamController@update']);

    $router->delete('team/{id}', ['uses' => 'TeamController@delete']);
});

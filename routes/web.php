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

// $router->get('/api/user', 'UserController@index');
// $router->get('/api/user/{id}', 'UserController@show');
// $router->post('/api/user', 'UserController@create');
// $router->put('/api/user/{id}', 'UserController@update');
// $router->delete('/api/user/{id}', 'UserController@delete');


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('user',  ['uses' => 'UserController@index']);

    $router->get('user/{id}', ['uses' => 'UserController@show']);

    $router->post('user', ['uses' => 'UserController@create']);

    $router->delete('user/{id}', ['uses' => 'UserController@delete']);

    $router->put('user/{id}', ['uses' => 'UserController@update']);
});

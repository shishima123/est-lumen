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

    $router->get('user/{id}', ['uses' => 'UserController@show']);

    $router->post('user', ['uses' => 'UserController@store']);

    $router->delete('user/{id}', ['uses' => 'UserController@delete']);

    $router->put('user/{id}', ['uses' => 'UserController@update']);
   
});

<<<<<<< HEAD
<<<<<<< HEAD

=======
Route::get('/verify/{code}', 'AuthController@verify');
Route::get('/resend-email/{id}','AuthController@resendEmail');
>>>>>>> 39511ae (check verified email and resend email)
=======
Route::get('/verify/{code}','AuthController@verify');
Route::get('/resend/{id}','AuthController@resendEmail');
>>>>>>> 393605f (Fix PR verify_email)
// Api for Team
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('team',  ['uses' => 'TeamController@index']);

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

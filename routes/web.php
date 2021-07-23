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


$router->post('users/login', 'UserController@login');
$router->post('users/register','UserController@register');
$router->get('users/register/verification/{token}', 'UserController@verifyEmail');
$router->get('users/me', 'UserController@me');
$router->post('users/logout', 'UserController@logout');

$router->post('password/email', 'PasswordController@postEmail');
$router->post('password/reset',[
    'as' => 'password.reset', 'uses' => 'PasswordController@postReset'
]);
$router->get('users/{id}', 'UserController@singleUser');
$router->put('users/edit', [
    'middleware' => 'check', 'uses' => 'UserController@editUser'
]);
$router->post('users/adduser', [
    'middleware' => 'check', 'uses' => 'UserController@addUser'
]);
$router->delete('users/delete/{id}', [
    'middleware' => 'check', 'uses' => 'UserController@deleteUser'
]);
$router->get('users/get/all', 'UserController@allUsers');
$router->get('list', 'UserController@listUsers');
$router->put('users/selfedit', 'UserController@selfEdit');
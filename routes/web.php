<?php

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


$router->get('/cart', 'CartController@index');
$router->post('/cart/{stockEntryId}', 'CartController@add');
$router->delete('/cart/{cartEntryId}', 'CartController@remove');

$router->get('/stock', 'StockController@index');

$router->get('/orders', 'OrdersController@index');
$router->post('/orders/initiate', 'OrdersController@initiate');
// TODO
$router->post('/orders/create', 'OrdersController@create');
$router->post('/orders/confirm', 'OrdersController@confirm');
$router->post('/orders/listen_to_hook', 'OrdersController@listen_to_hook');


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


$router->group(['prefix' => 'api', 'as' => 'api.'], function() use ($router) {

    // v1 API
    $router->group(['prefix' => 'v1', 'as' => 'v1.'], function() use ($router) {

        // tickets API
        $router->group(['prefix' => 'tickets', 'as' => 'tickets.'], function() use ($router) {
            $router->post('/', ['as' => 'store', 'uses' => 'TicketController@store']);
            $router->get('/', ['as' => 'index', 'uses' => 'TicketController@index']);
            $router->get('/{id}', ['as' => 'show', 'uses' => 'TicketController@show']);
            $router->put('/{id}/status', ['as' => 'updateStatus', 'uses' => 'TicketController@updateStatus']);
            $router->post('/{id}/update', ['as' => 'update', 'uses' => 'TicketController@update']);
            $router->delete('/{id}', ['as' => 'destroy', 'uses' => 'TicketController@destroy']);
            $router->delete('/destroy/batch', ['as' => 'destroyBatch', 'uses' => 'TicketController@destroyBatch']);

            // ticket comments API
            $router->group(['prefix' => 'comments', 'as' => 'comments.'], function() use ($router) {
                $router->post('/{id}', ['as' => 'store', 'uses' => 'TicketCommentController@store']);
                $router->put('/{id}', ['as' => 'store', 'uses' => 'TicketCommentController@update']);
            });
        });

    });
});
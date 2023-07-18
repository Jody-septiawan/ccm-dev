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
        $router->get('/tickets/{id}', ['as' => 'show', 'uses' => 'TicketController@show']);
        $router->post('/tickets/{id}/score', ['as' => 'show', 'uses' => 'TicketScoreController@store']);

        $router->group(['prefix' => 'tickets', 'middleware' => 'api.token', 'as' => 'tickets.'], function() use ($router) {
            $router->post('/', ['as' => 'store', 'uses' => 'TicketController@store']);
            $router->get('/', ['as' => 'index', 'uses' => 'TicketController@index']);
            $router->get('/data/statistics', ['as' => 'index', 'uses' => 'TicketController@statistics']);
            $router->put('/{id}/status', ['as' => 'updateStatus', 'uses' => 'TicketController@updateStatus']);
            $router->put('/{id}', ['as' => 'update', 'uses' => 'TicketController@update']);
            $router->delete('/{id}', ['as' => 'destroy', 'uses' => 'TicketController@destroy']);
            $router->delete('/destroy/batch', ['as' => 'destroyBatch', 'uses' => 'TicketController@destroyBatch']);

            // ticket comments API
            $router->group(['prefix' => 'comments', 'as' => 'comments.'], function() use ($router) {
                $router->post('/{id}', ['as' => 'store', 'uses' => 'TicketCommentController@store']);
                $router->put('/{id}', ['as' => 'update', 'uses' => 'TicketCommentController@update']);
            });

            // ticket templates API
            $router->group(['prefix' => 'templates', 'as' => 'templates.'], function() use ($router) {
                $router->get('/detail/{id}', ['as' => 'show.', 'uses' => 'TicketNotificationTemplateController@show']);
                $router->get('/datatable', ['as' => 'all.', 'uses' => 'TicketNotificationTemplateController@all']);
                $router->post('/', ['as' => 'store', 'uses' => 'TicketNotificationTemplateController@store']);
                $router->put('/{id}', ['as' => 'update', 'uses' => 'TicketNotificationTemplateController@update']);
                $router->delete('/{id}', ['as' => 'update', 'uses' => 'TicketNotificationTemplateController@destroy']);
                $router->delete('/destroy/batch', ['as' => 'update', 'uses' => 'TicketNotificationTemplateController@destroyBatch']);
            });
        });

    });
});
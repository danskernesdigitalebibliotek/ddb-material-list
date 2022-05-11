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

/* @var \Laravel\Lumen\Routing\Router $router */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => ['auth', 'version-switcher']], function () use ($router) {
    $router->get('/list/{listId}', 'v%version%\ListController@get');
    $router->head('/list/{listId}/{item}', 'v%version%\ListController@hasItem');
    $router->put('/list/{listId}/{item}', 'v%version%\ListController@addItem');
    $router->delete('/list/{listId}/{item}', 'v%version%\ListController@removeItem');
});

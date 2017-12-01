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

$app->get('/', function () use ($app) {
    return $app->version();
});
$app->post('jugador/perfil/show', 'PerfilController@show');
$app->post('jugador/perfil/update/image', 'PerfilController@updatePhoto');
$app->post('jugador/perfil/update/average', 'PerfilController@updateAverage');


$app->post('user/create', 'LoginController@create');
$app->post('user/login', 'LoginController@index');

$app->post('partida/create', 'PartidaController@create');
$app->post('partida/show', 'PartidaController@show');
$app->post('partida/show/one', 'PartidaController@showOne');
$app->post('partida/info_spinner', 'PartidaController@infoEquipos');
$app->post('partida/aceptar', 'PartidaController@aceptarPartida');

$app->post('equipo/create', 'EquipoController@create');
$app->post('equipo/show', 'EquipoController@show');

$app->post('pantalla/carga', 'CargaController@index');

$app->post('search/jugador', 'SearchController@jugador');
$app->post('search/equipo', 'SearchController@equipo');

$app->post('amigos/show', 'InfoJugadorController@amigosMostrar');

$app->post('invitar/jugador', 'NotificacionesController@invitar');
$app->post('notificaciones/show', 'NotificacionesController@show');
$app->post('notificaciones/estado', 'NotificacionesController@cambiarEstado');
$app->post('notificaciones/unirme/equipo', 'NotificacionesController@unirmeEquipo');







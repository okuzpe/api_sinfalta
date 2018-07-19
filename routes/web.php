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

$app->post('partida/libre', 'PartidaLibreController@cargarDatos');
$app->post('partida/libre/crear', 'PartidaLibreController@crearPartidaLibre');
$app->post('partida/libre/acciones', 'PartidaLibreController@acciones');

$app->post('equipo/create', 'EquipoController@create');
$app->post('equipo/show', 'EquipoController@show');
$app->post('equipo/setting', 'EquipoController@loadSetting');
$app->post('equipo/jugador/delete', 'EquipoController@deleteJugador');
$app->post('equipo/show/one', 'EquipoController@showOne');
$app->post('equipo/edit/foto', 'EquipoController@editFoto');
$app->post('equipo/edit/datos', 'EquipoController@editDatos');
$app->post('equipo/tiene', 'EquipoController@tieneEquipo');

$app->post('pantalla/carga', 'CargaController@index');

$app->post('search/jugador', 'SearchController@jugador');
$app->post('search/equipo', 'SearchController@equipo');

$app->post('amigos/show', 'InfoJugadorController@amigosMostrar');

$app->post('invitar/jugador', 'NotificacionesController@invitar');
$app->post('notificaciones/show', 'NotificacionesController@show');
$app->post('notificaciones/estado', 'NotificacionesController@cambiarEstado');
$app->post('notificaciones/unirme/equipo', 'NotificacionesController@unirmeEquipo');

$app->post('scan/retar', 'ScanController@retar');
$app->post('scan/amigo', 'ScanController@amigo');

$app->post('entrenamiento/cargar/alimentacion', 'EntrenamientoController@cargarAliementacion');
$app->post('entrenamiento/evaluacion/inicial', 'EntrenamientoController@guardarEntIni');
$app->post('entrenamientos/guardar', 'EntrenamientoController@guardarEntrenamiento');
$app->post('entrenamiento/historial', 'EntrenamientoController@historialEntrenamiento');
$app->post('entrenamiento/delete', 'EntrenamientoController@deleteEntrenamiento');







<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;
use DB;
use App\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Input;

class PartidaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        $partidas=DB::table('partida')
            ->where('id_estatus','=','1')
            ->select('id_partida','latitud','longitud','id_tipopartida')
            ->get();

        return response()->json(['partidas'=>$partidas,'success' => true]);
    }

    public function showOne(Request $request)
    {
        $id_partida=(int)$request->get("id_partida");
        $token=$request->get('api_token');

        $partida=DB::table('partida')
            ->join('equipo','partida.equipo_creador','=','equipo.id_equipo')
            ->select('equipo.nombre','equipo.reputacion_positiva','reputacion_negativa','equipo.id_tipoequipo','partida.fechahora_inicio','partida.descripcion','partida.id_tipopartida')
            ->where('partida.id_partida','=',$id_partida)
            ->first();

        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->first();

        $mis_equipos=DB::table('jugador_equipo')
            ->where('jugador_equipo.id_rangoequipo','<','3')
            ->where('jugador_equipo.id_jugador','=',$jugador->id_jugador)
            ->join('equipo','jugador_equipo.id_equipo','=','equipo.id_equipo')
            ->select('equipo.nombre','equipo.id_tipoequipo','jugador_equipo.id_rangoequipo')
            ->get();


        return response()->json(['success' => true,'partida'=>$partida,'mis_equipos'=>$mis_equipos]);
    }


    public function create(Request $request)
    {
        $api_token = trim($request->get('api_token'));
        $creador= DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $api_token)
            ->first()->id_jugador;

        $nombre=trim($request->get('nombre'));

        $equipo=DB::table('equipo')
            ->select('id_equipo','id_tipoequipo')
            ->where('nombre', '=', $nombre)
            ->first();

        $creador_rango=DB::table('jugador_equipo')
            ->select('id_rangoequipo')
            ->where('id_jugador', '=', $creador)
            ->where('id_equipo', '=', $equipo->id_equipo)
            ->first();


        $date =date_create($request->get('fechahora_inico'));
        $date->format('Y-m-d H:i:s');

        $partida = new Partida();

        $partida->id_estatus = 1;
        $partida->id_creador = $creador;
        $partida->id_tipopartida=$equipo->id_tipoequipo;
        $partida->longitud = $request->get('lon');
        $partida->latitud = $request->get('lat');
        $partida->descripcion = $request->get('descripcion');
        $partida->fechahora_inicio = $date;
        $partida->equipo_creador = $equipo->id_equipo;

        $cantidad_partidas_creadas=DB::table('partida')
            ->where('equipo_creador', '=', $equipo->id_equipo)
            ->where('id_estatus', '=', '1')
            ->count();


        if ($creador_rango=1 or $creador_rango=2) {
            if ($cantidad_partidas_creadas < 3) {
                if ($partida->save()) {
                    return response()->json(['success' => true, "estado" => "Partida creado exitosamente"]);
                } else {
                    return response()->json(['success' => false, "estado" => "No se pudo crear la partida"]);
                }
            } else {
                return response()->json(['success' => false, "estado" => "No se pudo crear la partida, no se puede tener mas de 3 partidas por equipo creadas al mismo tiempo"]);
            }
        }else{
            return response()->json(['success' => false, "estado" =>"no posee el rango suficiente para crear una partida con el equipo: ".$nombre]);
        }

    }

    public function infoEquipos(Request $request)
    {
        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->get();

        $equipos=DB::table('jugador_equipo')
            ->where('jugador_equipo.id_jugador','=',$jugador[0]->id_jugador)
            ->join('equipo','jugador_equipo.id_equipo','=','equipo.id_equipo')
            ->select('equipo.nombre','equipo.id_tipoequipo')
            ->get();

        if (!$equipos->isEmpty()){
            return response()->json(['success'=>true,'estado'=>'tiene equipos','equipos'=>$equipos]);
        }else{
            return response()->json(['success'=>false,'estado'=>'no tiene equipos','equipos'=>$equipos]);
        }

    }

    public function aceptarPartida(Request $request)
    {
        $id_partida =$request->get("id_partida");
        $nombre_equipo_retador = trim($request->get("nombre_equipo_retador"));

        $id_equipo_retador = DB::table('equipo')
            ->select('id_equipo')
            ->where('nombre', '=', $nombre_equipo_retador)
            ->first();


        $id_equipo_creador= DB::table('partida')
            ->select('id_creador')
            ->where('id_partida', '=', $id_partida)
            ->first();

        if ($id_equipo_creador->id_creador != $id_equipo_retador->id_equipo) {

            $estado = DB::table('partida')
                ->where('id_partida', '=', $id_partida)
                ->where('id_estatus','=',1)
                ->update(['id_estatus' => 4, 'equipo_retador' => $id_equipo_retador->id_equipo]);

            if ($estado) {
                return response()->json(['success' => true, 'estado' => 'Partida aceptada, para mas informacion vea la seccion: " Mis  partidas " ']);
            } else {
                return response()->json(['success' => false, 'estado' => 'No se pudo retar este equipo']);
            }

        }else{
            return response()->json(['success' => false, 'estado' => 'El equipo creador de la partida y con el que quieres retar son iguales. \n Seleciona otra equipo. ']);
        }
//        return response()->json(['success' =>true,'estado' =>$id_equiporetador->id_equipo."= ".$id_equipo_creador->id_creador ]);
    }
}

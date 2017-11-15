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
            ->select('id_partida','latitud','longitud')
            ->get();

        return response()->json(['partidas'=>$partidas,'success' => true]);
    }

    public function showOne(Request $request)
    {
        $id_partida=$request->get("id_partida");

        $partida=DB::table('partida')
            ->join('equipo','partida.equipo_creador','=','equipo.id_equipo')
            ->select('equipo.nombre','equipo.reputacion_positiva','reputacion_negativa','equipo.id_tipoequipo','partida.fechahora_inicio','partida.descripcion')
            ->where('partida.id_partida','=',$id_partida)
            ->get();

        return response()->json(['success' => true,'partida'=>$partida]);
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
            ->select('equipo.nombre')
            ->get();

        if (!$equipos->isEmpty()){
            return response()->json(['success'=>true,'estado'=>'tiene equipos','equipos'=>$equipos]);
        }else{
            return response()->json(['success'=>false,'estado'=>'no tiene equipos','equipos'=>$equipos]);
        }

    }
}

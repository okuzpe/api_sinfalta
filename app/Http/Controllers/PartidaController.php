<?php

namespace App\Http\Controllers;

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
        $partidas=DB::table('partida')->where('id_estatus','=','1')
            ->get();

        return response()->json(['partidas'=>$partidas,'success' => true]);


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
            ->where('nombre', '=', $nombre)
            ->select('id_equipo','id_tipoequipo')
            ->first();


        $partida = new Partida();

        $partida->id_estatus = 1;
        $partida->id_creador = $creador;
        $partida->id_tipopartida=$equipo->id_tipoequipo;
        $partida->longitud = $request->get('lon');
        $partida->latitud = $request->get('lat');
        $partida->descripcion = $request->get('descripcion');
        $partida->fechahora_inicio = $request->get('fechahora_inico');
        $partida->equipo_creador = $equipo->id_equipo;

        if($partida->save()) {
            return response()->json(['success' => true,"estado"=>"Partida creado exitosamente"]);
        }else{
            return response()->json(['success' => false,"estado"=>"No se pudo crear la partida"]);
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
            ->select('equipo.id_equipo','equipo.nombre')
            ->get();

        if (!$equipos->isEmpty()){
            return response()->json(['success'=>true,'estado'=>'tiene equipos','equipos'=>$equipos]);
        }else{
            return response()->json(['success'=>false,'estado'=>'no tiene equipos','equipos'=>$equipos]);
        }

    }
}

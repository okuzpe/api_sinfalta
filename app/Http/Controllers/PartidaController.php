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
        $partida=DB::table('partida')
            ->select('id_jugador')->where('');
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

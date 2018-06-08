<?php

namespace App\Http\Controllers;


use App\Jugador;
use App\Partida;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use JD\Cloudder\Facades\Cloudder;

class PartidaLibreController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');

    }

    public function crearPartidaLibre(Request $request)
    {
        $token=$request->get('api_token');
        $date=$request->get('fecha_hora');
        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->first();


        $partida = new Partida();

        $partida->id_estatus = 1;
        $partida->id_tipopartida=4;
        $partida->id_creador = $jugador->id_jugador;
        $partida->longitud = $request->get('lon');
        $partida->latitud = $request->get('lat');
        $partida->descripcion = $request->get('descripcion');
        $partida->fechahora_inicio = $date;
        $partida->equipo_creador = null;

        if ($partida->save()){
            return response()->json(['success' => true]);

        }else{
            return response()->json(['success' => false]);
        }




    }


    public function cargarDatos(Request $request)
    {
        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->first();



    }
}

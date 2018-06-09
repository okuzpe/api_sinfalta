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
            return response()->json(['success' => true,'id_partida'=>$partida->id_partida]);
        }else{
            return response()->json(['success' => false]);
        }




    }


    public function cargarDatos(Request $request)
    {

        $id_partida=$request->get('id_partida');

        $partida = DB::table('partida')
            ->select('descripcion')
            ->where('id_partida','=',$id_partida)
            ->first();


        $jugadores_partida=DB::table('jugador_partida_libre')
            ->join('jugador','jugador_partida_libre.id_jugador','=','jugador.id_jugador')
            ->select('jugador.id_jugador','jugador_partida_libre.id_tipo_equipo','jugador.tiene_imagen',
                'jugador.nombre','jugador.apodo')
            ->where('id_partida','=',$id_partida)
            ->get();

        return response()->json(['success' => true,'jugadores' => $jugadores_partida,'descripcion'=>$partida]);

    }
}

<?php
/**
 * Created by PhpStorm.
 * User: omar_
 * Date: 19/6/2017
 * Time: 10:27 AM
 */

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class SearchController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['create', 'index']]);

    }

    public function jugador(Request $request)
    {
        if ($request) {
            $query = trim($request->get('query'));
            $jugadores = DB::table('jugador')
                ->select('id_jugador','apodo','nombre','tiene_imagen')
                ->where('nombre', 'LIKE', $query . '%')
                ->where('api_token','<>',$request->get('api_token'))
                ->orderBy('apodo', 'desc')
                ->limit(10)
                ->get();

            return response()->json(['success' => true,'jugadores' => $jugadores]);
        }else{
            return response()->json(['success' => false]);
        }
    }


    public function equipo(Request $request)
    {
        if ($request) {

            $token=$request->get('api_token');
            $jugador = DB::table('jugador')
                ->select('id_jugador')
                ->where('api_token','=',$token)
                ->first();

            $query = trim($request->get('query'));
            $equipos = DB::table('equipo')
                ->join('jugador_equipo','jugador_equipo.id_equipo','=','equipo.id_equipo')
                ->select('equipo.id_equipo','equipo.nombre','equipo.reputacion_positiva','equipo.reputacion_negativa')
                ->where('equipo.nombre', 'LIKE', $query . '%')
                ->where('jugador_equipo.id_jugador','<>',$jugador->id_jugador)
                ->limit(10)
                ->get();

            return response()->json(['success' => true,'equipos' => $equipos]);
        }else{
            return response()->json(['success' => false]);
        }
    }
}


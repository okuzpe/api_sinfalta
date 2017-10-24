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

    public function equipo(Request $request)
    {
        if ($request) {
            $query = trim($request->get('query'));
            $id_jugador = DB::table('jugador')
                ->select('apodo')
                ->where('api_token', '=', $request->get('api_token'))
                ->first();

            $jugadores = DB::table('jugador')
                ->select('id_jugador','apodo','nombre','tiene_imagen')
                ->where('nombre', 'LIKE', $query . '%')
                ->orwhere('apodo', 'LIKE', $query . '%')
                ->where('api_token','<>',$request->get('api_token'))
                ->where('apodo','<>','%'.$id_jugador->apodo)
                ->orderBy('apodo', 'desc')
                ->limit(10)
                ->get();
            return response()->json(['success' => true,'jugadores' => $jugadores]);
        }else{
            return response()->json(['success' => false]);
        }
    }


    public function create(Request $request)
    {

    }
}

    {

}
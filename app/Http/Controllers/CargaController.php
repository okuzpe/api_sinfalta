<?php


namespace App\Http\Controllers;

use App\Equipo;
use App\JugadorEquipo;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use JD\Cloudder\Facades\Cloudder;

class CargaController extends Controller
{

    public function __construct()
    {
//        $this->middleware('auth');
    }

    public function index(Request $request){
        $id_jugador = DB::table('jugador')
            ->where( 'api_token','=',Input::get('api_token'))
            ->select('id_jugador')
            ->first()->id_jugador;

        $tiene =DB::table('jugador_equipo')
            ->where( 'id_jugador','=',$id_jugador)
            ->select('id_jugador','id_equipo','id_rangoequipo')
            ->get();

        if (!$tiene->isEmpty()){
            return response()->json(['success' => true,'tiene_equipo'=>true]);

        }else{
            return response()->json(['success' => true,'tiene_equipo'=>false]);
        }


    }
}

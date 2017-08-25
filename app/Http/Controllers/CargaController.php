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
            ->select('id_jugador','tiene_imagen')
            ->get();

        $tiene =DB::table('jugador_equipo')
            ->where( 'id_jugador','=',$id_jugador[0]->id_jugador)
            ->select('id_jugador','id_equipo','id_rangoequipo')
            ->get();

        if (!$tiene->isEmpty()){
            return response()->json(['success' => true,'tiene_equipo'=>true,'img_url'=>"https://res.cloudinary.com/hmb2xri8f/image/upload/fotoPerfil".$id_jugador[0]->id_jugador,'tiene_imagen'=>$id_jugador[0]->tiene_imagen.""]);

        }else{
            return response()->json(['success' => true,'tiene_equipo'=>false,'img_url'=>"https://res.cloudinary.com/hmb2xri8f/image/upload/fotoPerfil".$id_jugador[0]->id_jugador,'tiene_imagen'=>$id_jugador[0]->tiene_imagen.""]);
        }


    }
}

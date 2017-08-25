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

        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador','tiene_imagen')
            ->where('api_token','=',$token)
            ->get();



        $tiene =DB::table('jugador_equipo')
            ->where( 'id_jugador','=',$jugador[0]->id_jugador)
            ->select('id_jugador','id_equipo','id_rangoequipo')
            ->get();

        if (!$tiene->isEmpty()){
            return response()->json(['tiene_imagen'=>$jugador[0]->tiene_imagen,'success' => true,'tiene_equipo'=>true,'img_url'=>"https://res.cloudinary.com/hmb2xri8f/image/upload/fotoPerfil".$jugador[0]->id_jugador]);

        }else{
            return response()->json(['tiene_imagen'=>$jugador[0]->tiene_imagen,'success' => true,'tiene_equipo'=>false,'img_url'=>"https://res.cloudinary.com/hmb2xri8f/image/upload/fotoPerfil".$jugador[0]->id_jugador]);
        }
//

    }
}

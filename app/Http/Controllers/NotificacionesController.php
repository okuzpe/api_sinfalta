<?php


namespace App\Http\Controllers;

use App\Equipo;
use App\JugadorEquipo;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use JD\Cloudder\Facades\Cloudder;

class NotificacionesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request){

        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador','tiene_imagen')
            ->where('api_token','=',$token)
            ->get();

        $invitaciones_amigos=DB::table('invitaciones_amigos')
            ->where( 'id_jugador','=',$jugador[0]->id_jugador)
            ->select('id_jugador','id_equipo','id_rangoequipo')
            ->get();


        $invitaciones_partida=DB::table('invitaciones_partida')
            ->select('id_equipo_invitador','id_equipo_invitar','created_at')
            ->where( 'id_jugador','=',$jugador[0]->id_jugador)
            ->get();

        $invitaciones_equipo=DB::table('invitaciones_equipo')
            ->where( 'id_jugador','=',$jugador[0]->id_jugador)
            ->select('id_jugador','id_equipo','id_rangoequipo')
            ->get();


        $notificaciones =DB::table('jugador_equipo')
            ->where( 'id_jugador','=',$jugador[0]->id_jugador)
            ->select('id_jugador','id_equipo','id_rangoequipo')
            ->get();

        return response()->json(['success' => false]);
    }
}

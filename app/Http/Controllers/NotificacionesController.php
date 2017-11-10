<?php


namespace App\Http\Controllers;

use App\Equipo;
use App\JugadorEquipo;
use App\Notificaion;
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
    public function invitar(Request $request)
    {
        $invitar_jugador= $request->get('invitar_jugador');
        $bool = filter_var($invitar_jugador, FILTER_VALIDATE_BOOLEAN);
        if (!$bool) {

            $id_jugador = DB::table('jugador')
                ->join('jugador_equipo', 'jugador_equipo.id_jugador', '=', 'jugador.id_jugador')
                ->select('jugador.id_jugador', 'jugador_equipo.id_rangoequipo')
                ->where('jugador.api_token', '=', $request->get('api_token'))
                ->first();

            $existe_invitacion =  DB::table('notificacions')
                ->where('id_invitador', '=', $id_jugador->id_jugador)
                ->where('id_invitado', '=', Input::get('id_invitado'))
                ->where('id_equipo', '=', Input::get('id_equipo'))
                ->first();


            if ($id_jugador->id_rangoequipo == 1 or $id_jugador->id_rangoequipo == 2) {


                if (!is_null($existe_invitacion)) {
                    return response()->json(['success' => true, "estado" => "Ya se ha invitado a este jugador al equipo."]);
                } else {
                    $invitacion = new Notificaion();
                    $invitacion->id_invitador = $id_jugador->id_jugador;
                    $invitacion->id_invitado = $request->get('id_invitado');
                    $invitacion->id_equipo = $request->get('id_equipo');
                    $invitacion->id_estatus = 3;
                    if ($invitacion->save()) {
                        return response()->json(['success' => true, "estado" => "Jugador invitado."]);
                    } else {
                        return response()->json(['success' => false, "estado" => "Error inesperado"]);
                    }
                }

            } else {
                return response()->json(['success' => false, "estado" => "No tienes los privilegios para invitar"]);
            }
        }else{

            $id_jugador = DB::table('jugador')
                ->select('id_jugador','nombre')
                ->where('api_token', '=', $request->get('api_token'))
                ->first();
            $invitacion_amigo = new Notificaion();
            $invitacion_amigo->id_invitador=$id_jugador->id_jugador;
            $invitacion_amigo->id_invitado=$request->get('id_invitado');
            $invitacion_amigo->id_estatus=3;

            if(DB::table('notificaciones')
                ->where('id_invitador','=',$id_jugador->id_jugador)
                ->where('id_invitado','=',$request->get('id_invitado'))
                ->exists()){

                if (!DB::table('amigos')
                    ->where('id_jugador','=',$id_jugador->id_jugador)
                    ->where('id_amigo','=',$request->get('id_invitado'))
                    ->exists()){
                    if ($invitacion_amigo->save()){
                        return response()->json(['success' => true, "estado" => "El jugador se ha invitado a tu lista de amigos"]);

                    }else{
                        return response()->json(['success' => true, "estado" => "No se pudo invitar al jugador a tu lista de amigos"]);
                    }
                }else{
                    return response()->json(['success' => true, "estado" => "El jugador y usted ya son amigos"]);

                }
            }else{
                return response()->json(['success' => true, "estado" => "El jugador esta pendiente por aceptar la invitacion"]);

            }
        }
    }
}

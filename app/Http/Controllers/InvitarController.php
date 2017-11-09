<?php
/**
 * Created by PhpStorm.
 * User: omar_
 * Date: 19/6/2017
 * Time: 10:27 AM
 */

namespace App\Http\Controllers;

use App\InvitacionesEquipo;
use App\InvitacionesAmigos;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;


class InvitarController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['create', 'index']]);

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

            $existe_invitacion = InvitacionesEquipo::where('id_invitador', '=', $id_jugador->id_jugador)
                ->where('id_invitado', '=', Input::get('id_invitado'))
                ->where('id_equipo', '=', Input::get('id_equipo'))
                ->first();


            if ($id_jugador->id_rangoequipo == 1 or $id_jugador->id_rangoequipo == 2) {


                if (!is_null($existe_invitacion)) {
                    return response()->json(['success' => true, "estado" => "Ya se ha invitado a este jugador al equipo."]);
                } else {
                    $invitacion = new InvitacionesEquipo();
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
            $invitacion_amigo = new InvitacionesAmigos();
            $invitacion_amigo->id_invitador=$id_jugador->id_jugador;
            $invitacion_amigo->id_invitado=$request->get('id_invitado');
            $invitacion_amigo->id_estatus=3;

            if(!DB::table('invitaciones_amigos')
                ->where('id_invitador','=',$id_jugador->id_jugador)
                ->where('id_invitado','=',$request->get('id_invitado'))
                ->exists()){

                if (!DB::table('amigos')
                        ->where('id_jugador','=',$id_jugador->id_jugador)
                        ->where('id_amigo','=',$request->get('id_invitado'))
                        ->exists()){
                    if ($invitacion_amigo->save()){
                        return response()->json(['success' => true, "estado" => "El jugador ".$id_jugador->nombre." se ha invitado a tu lista de amigos"]);

                    }else{
                        return response()->json(['success' => true, "estado" => "No se pudo invitar al jugador".$id_jugador->nombre." a tu lista de amigos"]);
                    }
                }else{
                    return response()->json(['success' => true, "estado" => "El jugador ".$id_jugador->nombre." y usted ya son amigos"]);

                }
            }else{
                return response()->json(['success' => true, "estado" => "El jugador ".$id_jugador->nombre." esta pendiente por aceptar la invitacion"]);

            }
        }
    }


}
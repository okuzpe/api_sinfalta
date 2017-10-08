<?php
/**
 * Created by PhpStorm.
 * User: omar_
 * Date: 19/6/2017
 * Time: 10:27 AM
 */

namespace App\Http\Controllers;

use App\Invitaciones;
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

    public function invitarAEquipo(Request $request)
    {
//
//        $id_jugador = DB::table('jugador')
//            ->select('id_jugador')
//            ->where('api_token', '=', $request->get('api_token'))
//            ->first();
//
//        $existe_invitacion=Invitaciones::where('id_invitador', '=', $id_jugador->id_jugador)
//            ->where('id_invitado', '=', Input::get('id_invitado'))
//            ->where('id_equipo', '=', Input::get('id_equipo'))
//            ->first();
//
//
//
//        if (!is_null($existe_invitacion)){
//            return response()->json(['success' => true,"estado"=>"Ya se ha invitado a este jugador al equipo."]);
//        }else{
//            $invitacion= new Invitaciones();
//            $invitacion->id_invitador= $id_jugador->id_jugador;
//            $invitacion->id_invitado = $request->get('id_invitado');
//            $invitacion->id_equipo=$request->get('id_equipo');
//            if ($invitacion->save()){
//                return response()->json(['success' => true,"estado"=>"Jugador invitado."]);
//            }else{
//                return response()->json(['success' => false,"estado"=>"Error inesperado."]);
//            }
//        }

        return response()->json(['success' => true,"estado"=>$request->get('id_equipo')]);
    }


}
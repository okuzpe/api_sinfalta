<?php

namespace App\Http\Controllers;

use App\Notificaion;
use DateTime;
use DateTimeZone;
use DB;
use App\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Input;

class ScanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function retar(Request $request)
    {
        $api_token = $request->get('api_token');
        $nombre_retador=$request->get('nombre');
        $id_equipo_retar=$request->get('id_equipo_retar');

        $id_jugador_creador= DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $api_token)
            ->first()->id_jugador;

        $equipo=DB::table('equipo')
            ->select('id_equipo','id_tipoequipo')
            ->where('nombre', '=', $nombre_retador)
            ->first();

        $jugador_id_jugador=DB::table('jugador_equipo')
            ->select('id_jugador')
            ->where('id_rangoequipo', '=', $id_jugador_creador)
            ->where('id_equipo', '=', $equipo->id_equipo)
            ->first();

        $date =date_create($request->get('fechahora_inico'));
        $date->format('Y-m-d H:i:s');

        $partida = new Partida();

        $partida->id_estatus = 1;
        $partida->id_creador = $id_jugador_creador;
        $partida->id_tipopartida=$equipo->id_tipoequipo;
        $partida->longitud = $request->get('lon');
        $partida->latitud = $request->get('lat');
        $partida->descripcion = $request->get('descripcion');
        $partida->fechahora_inicio = $date;
        $partida->equipo_creador = $equipo->id_equipo;


        $existe_partida=DB::table('partida')
            ->where('equipo_creador', '=', $equipo->id_equipo)
            ->where('equipo_retador', '=', $id_equipo_retar)
            ->count();


        $cantidad_partidas_creadas=DB::table('partida')
            ->where('equipo_creador', '=', $equipo->id_equipo)
            ->where('id_estatus', '=', '1')
            ->count();

        //BUG DE LA CONDICION...
        if($existe_partida<1) {
//            $existe_partida=null;
            if ($equipo->id_equipo != $id_equipo_retar) {
                if ($cantidad_partidas_creadas < 3) {
//                    if ($partida->save()) {

                        $notificacion = new Notificaion();
                        $notificacion->id_partida=$partida->id_partida;
                        $notificacion->id_creador = $equipo->id_equipo; //fino-> id_equipo a creador de la partida
                        $notificacion->id_destino = $jugador_id_jugador->id_jugador;//fino-> id del capitan de equipo
                        $notificacion->id_equipo = $id_equipo_retar;//fino-> id_equipo a retar en la partida
                        $notificacion->id_tipo_notificacion = 3;
                        $notificacion->id_estatus = 3;

                        if ($notificacion->save() && $partida->save()) {
                            return response()->json(['success' => true, "estado" => "Solicitud de reto de partida enviada"]);
                        } else {
                            return response()->json(['success' => false, "estado" => "No se retar al equipo"]);
                        }
//                    } else {
//                        return response()->json(['success' => false, "estado" => "No se retar al equipo"]);
//                    }
                } else {
                    return response()->json(['success' => false, "estado" => "No se retar al equipo, no se puede tener mas de 3 partidas por equipo creadas al mismo tiempo"]);
                }
            } else {
                return response()->json(['success' => false, "estado" => "No puedes retar a tu mismo equipo"]);
            }
        }else{
            return response()->json(['success' => false, "estado" => "Ya se envio la solicitud de reto de partida"]);
        }
    }

    public function amigo(Request $request)
    {

        $id_jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $request->get('api_token'))
            ->first();

        $id_destino= DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=',  $request->get('id_destino'))
            ->first();
        $notificacion_amigo = new Notificaion();
        $notificacion_amigo->id_creador = $id_jugador->id_jugador;
        $notificacion_amigo->id_destino = $id_destino->id_jugador;
        $notificacion_amigo->id_estatus = 3;
        $notificacion_amigo->id_tipo_notificacion = 1;


        $existe_notificacion = DB::table('notificaciones')
            ->where('id_creador', '=', $id_jugador->id_jugador)
            ->where('id_destino', '=', $id_destino->id_jugador)
            ->count();
        if ($existe_notificacion >= 0) {
            if (!DB::table('amigos')
                ->where('id_jugador', '=', $id_jugador->id_jugador)
                ->where('id_amigo', '=', $id_destino->id_jugador)
                ->exists()
            ) {

                if ($notificacion_amigo->save()) {
                    return response()->json(['success' => true, "estado" => "En espera de que el jugador acepte la solicitud"]);

                } else {
                    return response()->json(['success' => true, "estado" => "No se pudo invitar al jugador a tu lista de amigos"]);
                }
            } else {
                return response()->json(['success' => true, "estado" => "El jugador y usted ya son amigos"]);

            }
        } else {
            return response()->json(['success' => true, "estado" => "El jugador esta pendiente por aceptar la invitacion"]);

        }
    }

}

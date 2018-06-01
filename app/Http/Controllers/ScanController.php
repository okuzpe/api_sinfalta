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
        $nombre_retador = $request->get('nombre');
        $id_equipo_retar = $request->get('id_equipo_retar');

        $id_jugador_creador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $api_token)
            ->first()->id_jugador;

        $equipo = DB::table('equipo')
            ->select('id_equipo', 'id_tipoequipo')
            ->where('nombre', '=', $nombre_retador)
            ->first();

        $jugador_id_jugador = DB::table('jugador_equipo')
            ->select('id_jugador')
            ->where('id_rangoequipo', '=', 1)
            ->where('id_equipo', '=', $equipo->id_equipo)
            ->first();

        $date = date_create($request->get('fechahora_inico'));
        $date->format('Y-m-d H:i:s');

        $partida = new Partida();

        $partida->id_estatus = 1;
        $partida->id_creador = $id_jugador_creador;
        $partida->id_tipopartida = $equipo->id_tipoequipo;
        $partida->longitud = $request->get('lon');
        $partida->latitud = $request->get('lat');
        $partida->descripcion = $request->get('descripcion');
        $partida->fechahora_inicio = $date;
        $partida->equipo_creador = $equipo->id_equipo;


        $existe_partida = DB::table('partida')
            ->where('equipo_creador', '=', $equipo->id_equipo)
            ->where('id_estatus', '=', 1)
            ->exists();

        $cantidad_partidas_creadas = DB::table('partida')
            ->where('equipo_creador', '=', $equipo->id_equipo)
            ->where('id_estatus', '=', '1')
            ->count();

//        $notificacion_existe=DB::table('notificaciones')
//            ->where('id_partida', '=', $nombre_retador)
//            ->where('nombre', '=', $nombre_retador)
//            ->exists();


//        BUG DE LA CONDICION...
            if (!$existe_partida){
                if ($equipo->id_equipo != $id_equipo_retar) {
                    if ($cantidad_partidas_creadas < 3) {
                        if ($partida->save()) {
                            $notificacion = new Notificaion();
                            $notificacion->id_partida = $partida->id_partida;
                            $notificacion->id_creador = $equipo->id_equipo; //fino-> id_equipo a creador de la partida
                            $notificacion->id_destino = $jugador_id_jugador->id_jugador;//fino-> id del capitan de equipo
                            $notificacion->id_equipo = $id_equipo_retar;//fino-> id_equipo a retar en la partida
                            $notificacion->id_tipo_notificacion = 3;
                            $notificacion->id_estatus = 3;

                            if ($notificacion->save()) {
                                return response()->json(['success' => true, "estado" => "Solicitud de reto de partida enviada"]);
                            } else {
                                return response()->json(['success' => false, "estado" => "No se pudo retar al equipo"]);
                            }
                        }else{
                            return response()->json(['success' => false, "estado" => "No se pudo retar al equipo"]);
                        }
                    } else {
                        return response()->json(['success' => false, "estado" => "No se retar al equipo, no se puede tener mas de 3 partidas por equipo creadas al mismo tiempo"]);
                    }
                } else {
                    if ($notificacion_existe>0) {
                        $notificacion = new Notificaion();
                        $notificacion->id_partida = $notificacion_existe->id_partida;
                        $notificacion->id_creador = $equipo->id_equipo; //fino-> id_equipo a creador de la partida
                        $notificacion->id_destino = $jugador_id_jugador->id_jugador;//fino-> id del capitan de equipo
                        $notificacion->id_equipo = $id_equipo_retar;//fino-> id_equipo a retar en la partida
                        $notificacion->id_tipo_notificacion = 3;
                        $notificacion->id_estatus = 3;

                        if ($notificacion->save()) {
                            return response()->json(['success' => true, "estado" => "Solicitud de reto de partida enviada"]);
                        } else {
                            return response()->json(['success' => false, "estado" => "No se pudo retar al equipo"]);
                        }
                    }

                }
            } else {
                return response()->json(['success' => false, "estado" =>"A la espera de que el equipo confirme el reto, igualmente la gente puede ingresar a esta partida" ]);
            }
//        return response()->json(['success' => false, "estado" =>$existe_partida,"id_e_c"=>$equipo->id_equipo,"id_e_r"=>$id_equipo_retar ]);

    }

    public function amigo(Request $request)
    {

        $id_jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $request->get('api_token'))
            ->first();

        $id_destino = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $request->get('id_destino'))
            ->first();
        $notificacion_amigo = new Notificaion();
        $notificacion_amigo->id_creador = $id_jugador->id_jugador;
        $notificacion_amigo->id_destino = $id_destino->id_jugador;
        $notificacion_amigo->id_estatus = 3;
        $notificacion_amigo->id_tipo_notificacion = 1;


        $existe_notificacion = DB::table('notificaciones')
            ->where('id_creador', '=', $id_jugador->id_jugador)
            ->where('id_destino', '=', $id_destino->id_jugador)
            ->exists();

        if ($id_jugador->api_token!=$id_destino->api_token) {

            if (!$existe_notificacion) {
                $son_amigos = DB::table('amigos')
                    ->where('id_jugador', '=', $id_jugador->id_jugador)
                    ->where('id_amigo', '=', $id_destino->id_jugador)
                    ->exists();
                if (!$son_amigos) {
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
        }else{
            return response()->json(['success' => true, "estado" => "No te puedes invitar a ti mismo como amigo"]);

        }
    }

}

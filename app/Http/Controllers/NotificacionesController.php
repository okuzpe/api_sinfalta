<?php


namespace App\Http\Controllers;


use App\amigos;
use App\Equipo;
use App\JugadorEquipo;
use App\Notificaion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class NotificacionesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function invitar(Request $request)
    {
        $invitar_jugador = $request->get('invitar_jugador');
        $bool = filter_var($invitar_jugador, FILTER_VALIDATE_BOOLEAN);
        if (!$bool) {
            // Invitacion equipo
            $id_jugador = DB::table('jugador')
                ->join('jugador_equipo', 'jugador_equipo.id_jugador', '=', 'jugador.id_jugador')
                ->select('jugador.id_jugador', 'jugador_equipo.id_rangoequipo')
                ->where('jugador.api_token', '=', $request->get('api_token'))
                ->first();

            $existe_invitacion = DB::table('notificaciones')
                ->where('id_creador', '=', $id_jugador->id_jugador)
                ->where('id_destino', '=', Input::get('id_destino'))
                ->where('id_equipo', '=', Input::get('id_equipo'))
                ->first();


            if ($id_jugador->id_rangoequipo == 1 or $id_jugador->id_rangoequipo == 2) {
                if (!is_null($existe_invitacion)) {
                    return response()->json(['success' => true, "estado" => "En espera de que el jugador acepte la invitacion."]);
                } else {

                    $notificacion = new Notificaion();
                    $notificacion->id_creador = $id_jugador->id_jugador;
                    $notificacion->id_destino = $request->get('id_destino');
                    $notificacion->id_equipo = $request->get('id_equipo');
                    $notificacion->id_destino = $request->get('id_destino');
                    $notificacion->id_estatus = 3;
                    $notificacion->id_tipo_notificacion = 2;
                    if ($notificacion->save()) {
                        return response()->json(['success' => true, "estado" => "Jugador invitado."]);
                    } else {
                        return response()->json(['success' => false, "estado" => "Error inesperado"]);
                    }
                }

            } else {
                return response()->json(['success' => false, "estado" => "No tienes los privilegios para invitar"]);
            }
        } else {
            $id_jugador = DB::table('jugador')
                ->select('id_jugador')
                ->where('api_token', '=', $request->get('api_token'))
                ->first();
            $notificacion_amigo = new Notificaion();
            $notificacion_amigo->id_creador = $id_jugador->id_jugador;
            $notificacion_amigo->id_destino = $request->get('id_destino');
            $notificacion_amigo->id_estatus = 3;
            $notificacion_amigo->id_tipo_notificacion = 1;


            $existe_notificacion = DB::table('notificaciones')
                ->where('id_creador', '=', $id_jugador->id_jugador)
                ->where('id_destino', '=', $request->get('id_destino'))
                ->count();
            if ($existe_notificacion >= 0) {

                if (!DB::table('amigos')
                    ->where('id_jugador', '=', $id_jugador->id_jugador)
                    ->where('id_amigo', '=', $request->get('id_destino'))
                    ->exists()
                ) {
                    if ($notificacion_amigo->save()) {
                        return response()->json(['success' => true, "estado" => "El jugador se ha invitado a tu lista de amigos"]);

                    } else {
                        return response()->json(['success' => true, "estado" => "No se pudo invitar al jugador a tu lista de amigos"]);
                    }
                } else {
                    return response()->json(['success' => true, "estado" => "El jugador y usted ya son amigos"]);

                }
            } else {
                return response()->json(['success' => true, "estado" => "El jugador esta pendiente por aceptar la invitacion"]);
//                return $existe_notificacion;
            }
        }
    }

    public function show(Request $request)
    {

        $api_token = $request->get("api_token");

        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $api_token)
            ->first()->id_jugador;


        $notificaciones = DB::table('notificaciones')
            ->join('jugador', 'jugador.id_jugador', '=', 'notificaciones.id_creador')
            ->select('notificaciones.id_notificacion', 'notificaciones.id_creador', 'jugador.nombre AS nombre_creador', 'notificaciones.id_destino',
                'notificaciones.id_tipo_notificacion', 'notificaciones.created_at', 'notificaciones.id_equipo')//,'equipo.nombre AS nombre_equipo'
            ->where('notificaciones.id_destino', '=', $jugador)
            ->where('notificaciones.id_estatus', '=', 3)
            ->orderBy('notificaciones.created_at', 'DESC')
            ->get();


        $i = 0;
        foreach ($notificaciones as $not) {
            if ($notificaciones[$i]->id_equipo != null) {
                $notificaciones[$i]->nombre_equipo = DB::table('equipo')
                    ->where('id_equipo', '=', $notificaciones[$i]->id_equipo)
                    ->select('nombre')
                    ->first();
                $i++;
            }
        }


        if (count($notificaciones) > 0) {
            return response()->json(['success' => true, 'notificaciones' => $notificaciones]);
        } else {
            return response()->json(['success' => false, 'notificaciones' => "no tiene"]);
        }
    }

    public function cambiarEstado(Request $request)
    {

        $aceptar=$request->get("aceptar");
        $id_notificacion = $request->get("id_notificacion");

        $notificacion=DB::table('notificaciones')
            ->select('id_tipo_notificacion','id_creador','id_destino','id_equipo')
            ->where('id_notificacion','=',$id_notificacion)
            ->first();


        if ($aceptar == true) {
            switch ($notificacion->id_tipo_notificacion) {
                case 1:
                    $amigo=new Amigos();
                    $amigo->id_jugador=$notificacion->id_destino;
                    $amigo->id_amigo=$notificacion->id_creador;
                    $amigo->save();
                    break;
                case 2:
                    $equipo=new JugadorEquipo();
                    $equipo->id_jugador=$notificacion->id_destino;
                    $equipo->id_equipo=$notificacion->id_equipo;
                    $equipo->id_rangoequipo=3;
                    $equipo->save();
                    break;
                case 3:
//                    $notificacion->id_creador = $equipo->id_equipo; //fino-> id_equipo a creador de la partida
//                    $notificacion->id_destino = $jugador_id_jugador->id_jugador;//fino-> id del capitan de equipo
//                    $notificacion->id_equipo = $id_equipo_retar;//fino-> id_equipo a retar en la partida

                    $hecho=DB::table('partida')
                        ->where('id_equipo', '=', $notificacion->id_creador)
                        ->update(['equipo_retador' => $notificacion->id_equipo]);

                    break;
                case 4:
                    $equipo=new JugadorEquipo();
                    $equipo->id_jugador=$notificacion->id_creador;
                    $equipo->id_equipo=$notificacion->id_equipo;
                    $equipo->id_rangoequipo=3;
                    $equipo->save();
                    break;
            }

            $estatus = 4;
            DB::table('notificaciones')
            ->where('id_notificacion', $id_notificacion)
            ->update(['id_estatus' => $estatus]);

            return response()->json(['success' => true]);
        } else {
            $estatus = 5;
            if(DB::table('notificaciones')
                ->where('id_notificacion', $id_notificacion)
                ->update(['id_estatus' => $estatus])){
                return response()->json(['success' => true]);
            }
        }

//        return response()->json(['success' => true]);

//        return response()->json($notificacion->id_tipo_notificacion);
    }

//    private function tipo_not($valor){
//
//    }

    public function unirmeEquipo(Request $request)
    {

        $id_equipo = $request->get('id_equipo');
        $token = $request->get('api_token');

        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $token)
            ->first();

        $check_esta_en_el_equipo = DB::table('jugador_equipo')
            ->select('id_jugador')
            ->where('id_equipo', '=', $id_equipo)
            ->where('id_jugador', '=', $jugador->id_jugador)
            ->first();


        if (count($check_esta_en_el_equipo) == 0) {
            $id_destino = DB::table('jugador_equipo')
                ->where('id_rangoequipo', '=', 1)
                ->where('id_equipo', '=', $id_equipo)
                ->select('id_jugador')
                ->get();

            $notificacion_existe = DB::table('notificaciones')
                ->where('id_creador', '=', $jugador->id_jugador)
                ->where('id_destino', '=',  $id_destino[0]->id_jugador)
                ->where('id_tipo_notificacion','=',4)
                ->get();

            if (count($notificacion_existe) == 0) {


                $notificacion = new Notificaion();
                $notificacion->id_creador = $jugador->id_jugador;

                $notificacion->id_destino = $id_destino[0]->id_jugador;

                $notificacion->id_equipo = $id_equipo;
                $notificacion->id_tipo_notificacion = 4;
                $notificacion->id_estatus = 3;

                if ($notificacion->save()) {
                    return response()->json(['success' => true, "estado" => "Se ha enviado la solicitud"]);
                } else {
                    return response()->json(['success' => false, "estado" => "No se pudo enviar la solicitud"]);

                }
            }else{
                return response()->json(['success' => true, "estado" => "En espera a que acepten tu solicitud"]);
            }

        } else {
            return response()->json("Usted ya pertenece a este equipo");
        }

//            return response()->json($id_destino[0]->id_jugador);
    }
}

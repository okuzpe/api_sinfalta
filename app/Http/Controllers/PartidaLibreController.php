<?php

namespace App\Http\Controllers;


use App\Jugador;
use App\JugadorPartidaLibre;
use App\Partida;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use JD\Cloudder\Facades\Cloudder;

class PartidaLibreController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');

    }

    public function crearPartidaLibre(Request $request)
    {
        $token=$request->get('api_token');
        $date=$request->get('fecha_hora');
        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->first();


        $partida = new Partida();

        $partida->id_estatus = 1;
        $partida->id_tipopartida=4;
        $partida->id_creador = $jugador->id_jugador;
        $partida->longitud = $request->get('lon');
        $partida->latitud = $request->get('lat');
        $partida->descripcion = $request->get('descripcion');
        $partida->fechahora_inicio = $date;
        $partida->equipo_creador = null;

        if ($partida->save()){
            return response()->json(['success' => true,'id_partida'=>$partida->id_partida]);
        }else{
            return response()->json(['success' => false]);
        }




    }


    public function cargarDatos(Request $request)
    {

        $id_partida=$request->get('id_partida');

        $partida = DB::table('partida')
            ->select('descripcion')
            ->where('id_partida','=',$id_partida)
            ->first();


        $jugadores_partida=DB::table('jugador_partida_libre')
            ->join('jugador','jugador_partida_libre.id_jugador','=','jugador.id_jugador')
            ->select('jugador.id_jugador','jugador_partida_libre.id_tipo_equipo','jugador.tiene_imagen',
                'jugador.nombre','jugador.apodo')
            ->where('id_partida','=',$id_partida)
            ->get();

        return response()->json(['success' => true,'jugadores' => $jugadores_partida,'descripcion'=>$partida->descripcion]);

    }

    public function acciones(Request $request)
    {
        $token=$request->get('api_token');
        $accion=$request->get('accion');
        $id_partida=$request->get('id_partida');

        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->first()->id_jugador;

        switch ($accion){
            case "salir_partida":
                if ($this->checkEstaPartida($jugador,$id_partida)) {
                    $accion = DB::table('jugador_partida_libre')
                        ->where('id_jugador', '=',$jugador)
                        ->where('id_partida', '=', $id_partida)
                        ->delete();
                    if ($accion) {
                        return response()->json(['success' => true]);
                    } else {
                        return response()->json(['success' => false,'estado'=>'Ha ocurrido un error']);
                    }
                }else{
                    return response()->json(['success' => false,'estado'=>'Usted no esta en ninguno de los equipos.']);
                }
                break;
            case "refresh":
                return $this->refreshPartida($id_partida);
                break;
            case "unir_rojo":
                if (!$this->checkEstaEnEquipos($jugador,$id_partida,3)) {

                    $partida_libre = new JugadorPartidaLibre();
                    $partida_libre->id_jugador = $jugador;
                    $partida_libre->id_partida = $id_partida;
                    $partida_libre->id_tipo_equipo = 3;

                    if ($partida_libre->save()) {
                        return response()->json(['success' => true]);
                    } else {
                        return response()->json(['success' => false,'estado'=>"ha ocurrido un error"]);
                    }
                }else{
                    return response()->json(['success' =>false,'estado'=>"ya esta en el equipo rojo, refresque la sala"]);
                }
                break;

            case "unir_azul":
                if (!$this->checkEstaEnEquipos($jugador,$id_partida,4)) {
                    $partida_libre = new JugadorPartidaLibre();
                    $partida_libre->id_jugador = $jugador;
                    $partida_libre->id_partida = $id_partida;
                    $partida_libre->id_tipo_equipo = 4;

                    if ($partida_libre->save()) {
                        return response()->json(['success' => true]);
                    } else {
                        return response()->json(['success' => false,'estado'=>"ha ocurrido un error"]);
                    }
                }else{
                    return response()->json(['success' => false,'estado'=>"ya esta en el equipo rojo, refresque la sala"]);
                }
                break;
            default:
                return response()->json(['success' => false,'estado'=>"ha ocurrido un error"]);
        }
    }

    function checkEstaPartida($jugador,$id_partida)
    {
        if (DB::table('jugador_partida_libre')
            ->where('id_jugador', '=', $jugador)
            ->where('id_partida', '=', $id_partida)
            ->first()) {
            return true;
        }else{
            return false;
        }
    }

    function refreshPartida($id_partida)
    {

        $partida = DB::table('partida')
            ->select('descripcion')
            ->where('id_partida','=',$id_partida)
            ->first();


        $jugadores_partida=DB::table('jugador_partida_libre')
            ->join('jugador','jugador_partida_libre.id_jugador','=','jugador.id_jugador')
            ->select('jugador.id_jugador','jugador_partida_libre.id_tipo_equipo','jugador.tiene_imagen',
                'jugador.nombre','jugador.apodo')
            ->where('id_partida','=',$id_partida)
            ->get();

        return response()->json(['success' => true,'jugadores' => $jugadores_partida,'descripcion'=>$partida->descripcion]);

    }

    function checkEstaEnEquipos($jugador, $id_partida, $id_tipo_equipo)
    {
        if (DB::table('jugador_partida_libre')
            ->where('id_jugador', '=', $jugador)
            ->where('id_partida', '=', $id_partida)
            ->where('id_tipo_equipo', '=', $id_tipo_equipo)
            ->first()) {
            return true;
        }else{
            return false;
        }
    }
}

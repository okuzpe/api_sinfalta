<?php

namespace App\Http\Controllers;


use App\Jugador;
use App\JugadorEstadisticas;

use Cloudinary;
use DB;

use Faker\Provider\File;
use Faker\Provider\Image;
use Illuminate\Support\Facades\Storage;
use JD\Cloudder\Facades\Cloudder;
use Validator;
use Illuminate\Http\Request;


class InfoJugadorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');

    }

    public function amigosMostrar(Request $request)
    {
        $token=$request->get('api_token');

        $id_jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token', '=', $token)
            ->first();

//        $amigos=DB::table('amigos')
//            ->join('jugador','jugador.id_jugador','=','amigos.id_jugador')
//            ->where('jugador.api_token','=',$token)
//            ->select('amigos.id_amigo')
//            ->get();

        $amigos=DB::table('amigos')
            ->select('id_amigo','id_jugador')
            ->orwhere('id_jugador','=',$id_jugador->id_jugador)
            ->orwhere('id_amigo','=',$id_jugador->id_jugador)
            ->get();

        if (!$amigos->isEmpty()) {
            if ($amigos[0]->id_amigo == $id_jugador->id_jugador) {
                $amigos[0]->id_amigo = $amigos[0]->id_jugador;

            }

            $i = 0;
            foreach ($amigos as $a) {
                $amigos[$i]->datos = DB::table('jugador')
                    ->where('id_jugador', '=', $amigos[$i]->id_amigo)
                    ->select('nombre', 'apodo', 'tiene_imagen')
                    ->get();
                $i++;
            }
            return response()->json(['success'=>true,'amigos'=>$amigos]);
        }else{
            $amigos=[];
            return response()->json(['success'=>true,'amigos'=>$amigos]);
        }


    }
}
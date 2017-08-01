<?php

namespace App\Http\Controllers;


use App\Jugador;

use Cloudinary;
use DB;

use Faker\Provider\File;
use Faker\Provider\Image;
use Illuminate\Support\Facades\Storage;
use JD\Cloudder\Facades\Cloudder;
use Validator;
use Illuminate\Http\Request;


class PerfilController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');

    }

    public function show(Request $request)
    {
//        $show = DB::table('jugador')
//            ->select('apodo', 'altura', 'pie_dominante', 'peso','id_jugador','asistencia', 'imgurl_perfil')
//            ->where('api_token', '=', $request->get('api_token'))
//            ->get();

        $show = DB::table('jugador')
            ->select('apodo', 'altura', 'pie_dominante', 'peso','id_jugador','asistencia', 'imgurl_perfil')
            ->where('api_token', '=', $request->get('api_token'))
            ->first();
        $estadisticas=DB::table('jugador_estadisticas')
            ->select('id_tipoequipo','partidos_ganados','partidos_jugados','goles','asistencia')
            ->where('id_jugador', '=',$show->id_jugador)
            ->get();

        $estadisticas->total_asistencia = count($estadisticas[0]->asistencia);


        if (!$show) {
            return response()->json(['success' => false]);
        } else{
            $id_jugador=$show->id_jugador;
            $img_jugador=$show->imgurl_perfil;
            $show->estadisticas=$estadisticas;
//            $show->partidos_ganados=$estadisticas->partidos_ganados;
//            $show->partidos_jugados=$estadisticas->partidos_jugados;
//            $show->goles=$estadisticas->goles;
            //$URL_PERFIL= Cloudder::secureShow('fotoPerfil'.$show[0]->id_jugador,array ("width" => 250, "height" => 250));

            $URL_PERFIL="https://res.cloudinary.com/hmb2xri8f/image/upload/fotoPerfil$id_jugador";
            return response()->json(['datos' => $show, 'success' => true,'url_perfil'=>$URL_PERFIL,'tiene_img'=>$img_jugador]);     //,'url_perfil'=>$URL_PERFIL]
        }
    }

    public function updatefoto(Request $request)
    {
//        $validator = Validator::make($request->all(), [
//            'photo' => 'required',  // required| SEPONE ESTO mimes:jpeg,bmp,png'
//            'apodo'=>'required',
//            'altura'=>'required',
//            'peso'=>'required',
//            'pie_dominante'=>'required'
//        ]);

        $token=$request->get('api_token');
        $jugador=DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->get();


//        $datos =  Jugador::find($jugador[0]->id_jugador);
//        $datos->apodo=$request->get('apodo');
//        $datos->altura=$request->get('altura');
//        $datos->peso=$request->get('peso');
//        $datos->pie_dominante=$request->get('pie_dominante');
//
//
//        //OPC Foto
//        $publicId="fotoPerfil".$jugador[0]->id_jugador;
//
//
//        if (!$validator->fails()) {
//            $file = $request->get('photo');
//
//            if(Cloudder::upload("data:image/png;base64,".$file,$publicId,array("width" => 250, "height" => 250))){
//                $datos->imgurl_perfil=1;
//            }
//            $datos->update();
//            return response()->json(['success' => true]);
//        }else{
//            return response()->json(['success' => false]);
//        }
    }

    public function updateAverage(Request $request)
    {
        $token=$request->get('api_token');
        $jugador=DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->get();

        $dato=Jugador::find($jugador[0]->id_jugador);

        $average=$request->get('average');

        $position=(int)trim($request->get('position'));

        switch (intval($position))
        {
            case 0:
                $dato->apodo=$average;
                break;
            case 1:
                $dato->altura=$average;
                break;
            case 2:
                $dato->pie_dominante=$average;
                break;
            case 3:
                $dato->peso=$average;
                break;
        }

        if ( $dato->update()){
            return response()->json(['success' => true]);
        }else{
            return response()->json(['success' => false]);
        }

//        return response()->json(['success' => $jugador]);



    }

}
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
        $show = DB::table('jugador')
            ->select('apodo', 'altura', 'pie_dominante', 'peso','id_jugador',
                'partidos_jugados', 'partidos_ganados', 'asistencia', 'imgurl_perfil')
            ->where('api_token', '=', $request->get('api_token'))
            ->get();
        if ($show->isEmpty()) {
            return response()->json(['success' => false]);
        } else{
            $id_jugador=$show[0]->id_jugador;
            //$URL_PERFIL= Cloudder::secureShow('fotoPerfil'.$show[0]->id_jugador,array ("width" => 250, "height" => 250));
            $URL_PERFIL="https://res.cloudinary.com/hmb2xri8f/image/upload/fotoPerfil$id_jugador";
            return response()->json(['datos' => $show, 'success' => true,'url_perfil'=>$URL_PERFIL]);//,'url_perfil'=>$URL_PERFIL]
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'photo' => 'required',  // required| SEPONE ESTO mimes:jpeg,bmp,png'
//            'apodo'=>'required',
//            'altura'=>'required',
//            'peso'=>'required',
            'pie_dominante'=>'required'
        ]);

        $token=$request->get('api_token');
        $jugador=DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->get();

        $datos =  Jugador::find($jugador[0]->id_jugador);
        $datos->apodo=$request->get('apodo');
        $datos->altura=$request->get('altura');
        $datos->peso=$request->get('peso');
        $datos->pie_dominante=$request->get('pie_dominante');


        //OPC Foto
        $publicId="fotoPerfil".$jugador[0]->id_jugador;


        if (!$validator->fails()) {
            $file = $request->get('photo');

            Cloudder::upload("data:image/png;base64,".$file,$publicId,array("width" => 250, "height" => 250));
            $datos->update();
            return response()->json(['success' => true]);
        }else{
            return response()->json(['success' => false]);
        }
    }
}
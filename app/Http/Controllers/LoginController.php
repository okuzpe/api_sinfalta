<?php

namespace App\Http\Controllers;


use App\Jugador;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use JD\Cloudder\Facades\Cloudder;

class LoginController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['create', 'index']]);

    }

    public function index(Request $request)
    {
        $email = $request->input('email');
        $clave = $request->input('clave');
    if($email!=null and $clave!=null  ) {

        $login = DB::table('jugador')
            ->select('clave', 'api_token','id_jugador')
            ->where('email', '=', $email)
            ->get();
        $checkPass = Hash::check($clave, $login[0]->clave);
        if ($checkPass) {
            $options=null;
            $URL_PERFIL=Cloudder::show("fotoPerfil".$login[0]->id_jugador, array ($options));
            return response()->json(['api_token' => $login[0]->api_token, 'success' => true,'img_perfil'=>$URL_PERFIL]);
        } else {
            return response()->json(['success' => false]);
        }

    }else{return response()->json(['success' => false]);}

    }


    public function create(Request $request)
    {

        $jugador = new Jugador;
        if (Jugador::where('email', '=', Input::get('email'))->exists()) {
            return response()->json(['success' => false]);
        } else {
            $jugador->id_estatus = '1';
            $jugador->clave = Hash::make($request->get('clave'));
            $jugador->email = $request->get('email');
            $jugador->nombre = $request->get('nombre');
            $jugador->sexo = $request->get('sexo');
            $jugador->fecha_nacimiento = $request->get('fecha_nacimiento');
            $jugador->api_token = str_random(64);
            $jugador->save();

            return response()->json(['api_token' => $jugador->api_token, 'success' => true]);
        }
    }
}

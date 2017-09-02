<?php
/**
 * Created by PhpStorm.
 * User: omar_
 * Date: 17/6/2017
 * Time: 4:56 PM
 */

namespace App\Http\Controllers;

use App\Equipo;
use App\JugadorEquipo;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use JD\Cloudder\Facades\Cloudder;

class EquipoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Request $request)
    {
        $imagenCreada=false;


        if (Equipo::where('nombre', '=', Input::get('nombre'))->exists()) {
            return response()->json(['success' => true,'estado'=>'El equipo ya existe']);
        } else {
            $api_token = $request->get('api_token');

            $creador= DB::table('jugador')
                ->select('id_jugador')
                ->where('api_token', '=', $api_token)
                ->first()->id_jugador;

            $equipo = new Equipo;

            $file= $request->get('img_equipo');
            $equipo->nombre = $request->get('nombre');
            $equipo->lugar = $request->get('lugar');
            $equipo->descripcion = $request->get('descripcion');
            $equipo->id_tipoequipo = (int)$request->get('id_tipoequipo');
            $equipo->id_estatus =1;
            $equipo->id_creador_equipo=$creador;

            $publicId="fotoEquipo".$equipo->nombre;
            if ($file) {
                if(Cloudder::upload("data:image/png;base64," . $file, $publicId, array("width" => 250, "height" => 250))){
                    $imagenCreada=true;
                    $equipo->tiene_imagen=1;
                }
            }
            if($equipo->save()) {
                return response()->json(['success' => true,'estado'=>'Equipo creado exitosamente','img_creada'=>$imagenCreada]);
            }else{
                return response()->json(['success' => true,'estado'=>'No se pudo crear el equipo','img_creada'=>$imagenCreada,400]);
            }
        }

    }
}

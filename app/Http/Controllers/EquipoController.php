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
use Helpers;

class EquipoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Request $request)
    {
        $imagenCreada=false;

        $existe_equipo=Equipo::where('nombre', '=', Input::get('nombre'))->first();;

        if (is_null($existe_equipo)) {
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
                return response()->json(['success' => true,"estado"=>"Equipo creado exitosamente",'img_creada'=>$imagenCreada]);
            }else{
                return response()->json(['success' => false,"estado"=>"No se pudo crear el equipo",'img_creada'=>$imagenCreada]);
            }
        } else {
            return response()->json(['success' => false,"estado"=>"El equipo ya existe"]);
        }

    }
    public function show(Request $request)
    {

        $token=$request->get('api_token');

        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->get();

        $equipos=DB::table('jugador_equipo')
            ->where('id_jugador','=',$jugador[0]->id_jugador)
            ->join('equipo','equipo.id_equipo','=','jugador_equipo.id_equipo')
            ->select('equipo.nombre','jugador_equipo.id_equipo','jugador_equipo.id_rangoequipo','equipo.tiene_imagen')
            ->get();

        if (count($jugador) and count($equipos)) {
            $i = 0;
            foreach ($equipos as $e) {

//                $equipos[$i]->id_equipo;
                $equipos[$i]->jugadores_del_equipo = DB::table('jugador_equipo')
                    ->where('id_equipo', '=', $equipos[$i]->id_equipo)
                    ->join('jugador','jugador_equipo.id_jugador','=','jugador.id_jugador')
                    ->select('jugador_equipo.id_jugador', 'jugador_equipo.id_rangoequipo','jugador.nombre','jugador.tiene_imagen')
                    ->get();

                $i++;

            }
            return response()->json(['success'=>true,'equipos'=>$equipos]);
        }else{
            return response()->json(['success'=>false]);
        }

    }

    public function loadSetting(Request $request)
    {
        $id_equipo=$request->get('id_equipo');
        $token=$request->get('api_token');

        $id_jugador=DB::table('jugador')
            ->where('api_token', '=', $token)
            ->select('id_jugador')
            ->first();



        $datosEquipo=DB::table('equipo')
            ->where('id_equipo', '=', $id_equipo)
            ->select('nombre','lugar','descripcion')
            ->first();

        $jugadores = DB::table('jugador_equipo')
            ->join('jugador','jugador_equipo.id_jugador','=','jugador.id_jugador')
            ->select('jugador_equipo.id_jugador', 'jugador_equipo.id_rangoequipo','jugador.nombre','jugador.tiene_imagen')
            ->where('jugador_equipo.id_equipo', '=', $id_equipo)
            ->where('jugador.id_jugador', '<>', $id_jugador->id_jugador)
            ->get();

        if ($jugadores!=null && $datosEquipo!=null && $id_jugador!=null){
            return response()->json(['success'=>true,'datos_equipo'=>$datosEquipo,'jugadores'=>$jugadores,'id_jugador'=>$id_jugador->id_jugador]);

        }else{
            return response()->json(['success'=>true]) ;
        }

    }

    public function deleteJugador(Request $request){
        $id_equipo=$request->get('id_equipo');
        $id_jugador=$request->get('id_jugador');
        $token=$request->get('api_token');


        $delete=DB::table('jugador_equipo')
            ->where('id_equipo', '=', $id_equipo)
            ->where('id_jugador', '=', $id_jugador)
            ->delete();
        if ($delete){
            return response()->json(['success'=>true]) ;
        }else{
            return response()->json(['success'=>false]) ;
        }

    }

    public function showOne(Request $request){
        $token=$request->get('api_token');
        $id_equipo=$request->get('id_equipo');

        $equipo= DB::table('equipo')
            ->where('id_equipo', '=', $id_equipo)
            ->select('id_equipo','nombre')
            ->first();

        $jugadores_equipo=DB::table('jugador_equipo')
            ->where('id_equipo', '=', $id_equipo)
            ->join('jugador','jugador.id_jugador','=','jugador_equipo.id_jugador')
            ->select('jugador.id_jugador','jugador.nombre','jugador_equipo.id_rangoequipo','jugador.tiene_imagen')
            ->get();

        if ($equipo && $jugadores_equipo){
            return response()->json(['success'=>true,'equipo'=>$equipo,'jugadores_equipo'=>$jugadores_equipo]) ;
        }else{
            return response()->json(['success'=>false]) ;
        }
    }

    public function editFoto(Request $request)
    {
        $imagenCreada=false;


            $file= $request->get('img');
            $nombre = $request->get('nombre');

            $publicId="fotoEquipo".$nombre;
            if ($file) {
                if(Cloudder::upload("data:image/png;base64," . $file, $publicId, array("width" => 250, "height" => 250))){
                    return response()->json(['success' => true,'img_guardada'=>$imagenCreada]);
                }else{
                    return response()->json(['success' => false,'img_guardada'=>$imagenCreada]);
                }
            }else{
                return response()->json(['success' => false,'img_guardada'=>$imagenCreada]);
            }
    }

    public function editDatos(Request $request)
    {
        $dato= $request->get('dato');
        $id_dato = $request->get('id_dato');
        $id_equipo=$request->get('id_equipo');

        if ((int)$id_dato==1) {
            $hecho=DB::table('equipo')
                ->where('id_equipo', '=', $id_equipo)
                ->update(['nombre' => $dato]);
            return response()->json(['success' => true,'respuesta'=>'Nombre del equipo cambiado a '.$dato]);
        }else if ($id_dato==2){
            $hecho=DB::table('equipo')
                ->where('id_equipo', '=', $id_equipo)
                ->update(['lugar' => $dato]);
            return response()->json(['success' => true,'respuesta'=>'Lugar del equipo cambiado a '.$dato]);
        }else if ($id_dato==3){
            $hecho=DB::table('equipo')
                ->where('id_equipo', '=', $id_equipo)
                ->update(['descripcion' => $dato]);
            return response()->json(['success' => true,'respuesta'=>'Descripcion del equipo cambiada a '.$dato]);
        }else{
            return response()->json(['success' => false,'respuesta'=>'Error, no se pudo cambiar el dato a '.$dato]);
        }
    }

    public function tieneEquipo(Request $request){
        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->first();

        $tiene =DB::table('jugador_equipo')
            ->where( 'id_jugador','=',$jugador->id_jugador)
            ->select('id_jugador','id_equipo','id_rangoequipo')
            ->count();

        if($tiene>0){
            $tiene_equipo=true;
        }else{
            $tiene_equipo=false;
        }
        return response()->json(['success' => true,'tiene_equipo'=>$tiene_equipo]);

    }

}

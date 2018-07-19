<?php


namespace App\Http\Controllers;

use App\Equipo;
use App\JugadorEntrenamiento;
use App\JugadorEntrenamientos;
use App\JugadorEquipo;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use JD\Cloudder\Facades\Cloudder;

class EntrenamientoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function guardarEntIni(Request $request){

        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador','sexo','fecha_nacimiento')
            ->where('api_token','=',$token)
            ->get();
        $peso=$request->get('peso');
        $altura=$request->get('altura');
        $factor_tmb=(double)$request->get('factor_tmb');
        $meta=$request->get('meta');
        $edad=(int)$jugador[0]->fecha_nacimiento;

        if ($jugador[0]->sexo=="Masculino"){
            $val_sexo=5;
        }else{
            $val_sexo=-161;
        }

        $entrenamiento=new JugadorEntrenamiento();

        $imc=$peso/($altura*$altura);
        $tmb=((10*$peso)+(6.25*$altura*100)-(5*$edad)+$val_sexo)*$factor_tmb;

        $entrenamiento->id_jugador=$jugador[0]->id_jugador;
        $entrenamiento->imc=$imc;
        $entrenamiento->tmb=$tmb;
        $entrenamiento->meta=$meta;

        if (!DB::table('jugador_entrenamiento')
            ->where('id_jugador','=',$jugador[0]->id_jugador)
            ->count()){
            if ($entrenamiento->save()){
                return response()->json(['success' => true,'entrenamiento' => $entrenamiento]);
            }else{
                return response()->json(['success' => false]);
            }
        }else{
            if($update = DB::table('jugador_entrenamiento')
                ->where('id_jugador','=',$jugador[0]->id_jugador)
                ->update(['imc' => $imc,'tmb'=>$tmb,'meta'=>$meta])){

                $updated=DB::table('jugador_entrenamiento')
                    ->select('tmb','imc','meta')
                    ->where('id_jugador','=',$jugador[0]->id_jugador)
                    ->first();
                return response()->json(['success' => true,'entrenamiento' =>$updated]);
            }else{
                return response()->json(['success' => false]);
            }
        }
    }

    public function guardarEntrenamiento(Request $request){
        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador')
            ->where('api_token','=',$token)
            ->get();

        $entrenamiento=new JugadorEntrenamientos();
        $entrenamiento->fecha_hora=$request->get('date_time');
        $entrenamiento->calorias_quemadas=$request->get('calorias_quemadas');
        $entrenamiento->id_jugador=$jugador[0]->id_jugador;

        if ($entrenamiento->save()){
            return response()->json(['success' => true]);
        }else{
            return response()->json(['success' => false]);
        }
    }

    public function historialEntrenamiento(Request $request){
        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador','sexo','fecha_nacimiento')
            ->where('api_token','=',$token)
            ->get();

        $historial = DB::table('jugador_entrenamientos')
            ->select('fecha_hora','calorias_quemadas','id_entrenamiento')
            ->where('id_jugador','=',$jugador[0]->id_jugador)
            ->get();

        if ($historial->count()){
            return response()->json(['success' => true,'historial'=>$historial]);
        }else{
            return response()->json(['success' => false]);
        }
    }

    public function deleteEntrenamiento(Request $request){
        $id_historial=$request->get('id_historial');


        if (DB::table('jugador_entrenamientos')
            ->where('id_entrenamiento','=',$id_historial)
            ->delete()){
            return response()->json(['success' => true]);
        }else{
            return response()->json(['success' => false]);
        }
    }

    public function cargarAliementacion(Request $request){
        $token=$request->get('api_token');
        $jugador = DB::table('jugador')
            ->select('id_jugador','sexo','fecha_nacimiento')
            ->where('api_token','=',$token)
            ->get();

        $alimentacion=DB::table('jugador_entrenamiento')
            ->select('meta')
            ->where('id_jugador','=',$jugador[0]->id_jugador)
            ->get();

        if ($alimentacion->count()){
            return response()->json(['success' => true,'alimentacion'=>$alimentacion]);
        }else{
            return response()->json(['success' => false]);
        }
    }
}

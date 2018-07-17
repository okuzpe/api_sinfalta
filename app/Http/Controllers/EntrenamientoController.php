<?php


namespace App\Http\Controllers;

use App\Equipo;
use App\JugadorEntrenamiento;
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
        $factor_tmb=$request->get('factor_tmb');
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

        if ($jugador ==null){
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
}

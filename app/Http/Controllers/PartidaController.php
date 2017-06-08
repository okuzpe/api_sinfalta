<?php

namespace App\Http\Controllers;

use DB;
use App\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Input;

class PartidaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $partidas=DB::table('partida')->where('id_estatus','=','1')
            ->get();

//        for ($i=1;$i<=count($partidas);$i++){
//            return response()->json([$partidas,'success' => true]);
//        }
        return response()->json(['partidas'=>$partidas,'success' => true]);




//        foreach ($partidas as $partida){
//            return response()->json(['id'=>$partida,'success' => true]);
//            return response()->json($partida);
//            var_dump($partida->id_partida);
//           return response()->json(['id_partida'=>$partida->id_partida,$partida,'success' => true]);
//        }

    }

    public function create(Request $request)
    {
        $jugador=DB::table('jugador as j')
            ->select('id_jugador')->where('');
    }
}

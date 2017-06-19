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

    public function show(Request $request)
    {
        $partidas=DB::table('partida')->where('id_estatus','=','1')
            ->get();

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
        $partida=DB::table('partida')
            ->select('id_jugador')->where('');
    }
}

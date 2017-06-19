<?php
/**
 * Created by PhpStorm.
 * User: omar_
 * Date: 19/6/2017
 * Time: 10:27 AM
 */

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class SearchController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth', ['except' => ['create', 'index']]);

    }

    public function equipo(Request $request)
    {
        if ($request) {
            $query = trim($request->get('query'));
            $jugadores = DB::table('jugador')
                ->select('id_jugador','apodo')
                ->where('apodo', 'LIKE', '%' . $query . '%')
                ->orderBy('apodo', 'desc')
                ->get();
            return response()->json(['success' => true,'jugadores' => $jugadores]);
        }else{
            return response()->json(['success' => false]);
        }
    }


    public function create(Request $request)
    {

    }
}

    {

}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\User;
use DB;

class LoginController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except' => ['create','index']]);

    }
    public function index(Request $request)
    {
        try{
            $email = $request->input('email');
            $clave = $request->input('clave');
            $login=DB::table('jugador as j')
                ->select('email','clave','api_token')
                ->where('email','=',$email)
                //->where('clave','=',$clave)
                ->get();
            $checkPass=Hash::check($clave, $login[0]->clave);
            if ($login and $checkPass){
                return response()->json(['api_token'=>$login[0]->api_token,'success'=>true]);
            }else{
                return response()->json(['success'=>false]);
            }
        }catch(\Exception $e){
            return response()->json(['success'=>false]);
        }



    }

    public function show($id)
    {
        return User::findOrFail($id);

    }
    public function create(Request $request)
    {
        try{
        $usuario=new User;
        $usuario->id_estatus=('1');
        $usuario->clave=Hash::make($request->get('clave'));
        $usuario->email=$request->get('email');
        $usuario->nombre=$request->get('nombre');
        $usuario->fecha_nacimiento=$request->get('fecha_nacimiento');
        $usuario->api_token=str_random(64);
        $saved=$usuario->save();
        return response()->json(['api_token'=>$usuario->api_token,'success'=>true]);
        }catch(QueryException $ex){ 
          //dd($ex->getMessage()); 
          return response()->json(['success'=>false]);

        }
       
    }
}

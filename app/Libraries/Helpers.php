<?php

use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: omar_
 * Date: 26/03/2018
 * Time: 17:51
 */

function cambiarDatosEquipo($col,$dato,$id_equipo)
{
    $hecho=DB::table('equipo')
        ->where('id_equipo', '=', $id_equipo)
        ->update([$col => $dato]);
    return  (bool) $hecho;
}
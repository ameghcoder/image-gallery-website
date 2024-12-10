<?php

namespace App\Controllers;

use App\Models\Temps;

class Temp{
    public function start(){
        return Temps::add();
    }
}
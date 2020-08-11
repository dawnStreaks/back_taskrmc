<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Helpers\Camunda ;
use Illuminate\Routing\Controller AS MasterController;

class Controller extends MasterController
{
    protected $camunda ;
    protected $user = null ;
    public function __construct()
    {
        $this->camunda = new Camunda(env('JWT_SECRET'));
        $this->set_user();
    }

    public function set_user(){
        if(\JWTAuth::parseToken()){
            $this->user = \JWTAuth::parseToken()->authenticate() ;
        }
    }

    public function validateFunction($function , $validationRequired = true){

        if(!$validationRequired){
            return $function ;
        }
        if ($function != false){
            return $function ;
        }

        return false ;

    }

}

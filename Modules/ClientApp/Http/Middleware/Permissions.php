<?php

namespace Modules\ClientApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Permissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next , $permissions = null , $routeType = null)
    {
        $resourceId = null ;
        if(!isset($permissions)){
            return response()->json(["code" => 400 , "msg" => "Invalid route params !(permissions)"] , 400);
        }
        if($permissions = explode("|",$permissions) AND (isset($permissions[0]) AND isset($permissions[1]))){
            $permission = $permissions[0] ;
            $resourceType = $permissions[1] ;
        }
        if(isset($permissions[2])){
            $resourceId = $permissions[2];
        }elseif($routeType == "dynamic"){
            $resourceId = $request->route('id');
        }

        if(!isset($permissions[0]) || !isset($permissions[1])){
            return response()->json(["code" => 400 , "msg" => "Sorry all route permission params are required !"] , 400);
        }
        if(!\JWTAuth::parseToken()->authenticate()->hasAccess($permission,$resourceType,$resourceId)){
            return response()->json(["code" => 401 , "msg" => "You don't have a permission to access !"]);
        }
        return $next($request);
    }
}

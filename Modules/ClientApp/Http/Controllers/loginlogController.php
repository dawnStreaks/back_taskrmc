<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class loginlogController extends Controller
{
    //
    public function index()
    {
        $loginlogs = \DB::table("login_activities")
            ->join('users', 'login_activities.user_id', '=', 'users.id')
            ->select('login_activities.*','users.name','users.last_name')
            ->where('login_activities.created_at', '>=', date(Carbon::today()))
            ->get();
        $i=0;
        foreach($loginlogs as $loginlog){
            $temp1   = explode('/',$loginlog->user_agent);
            $loginlogs[$i]->user_agent= $temp1[0];
            $loginlogs[$i]->name=$loginlog->name." ".$loginlog->last_name;
            $i++;
        }
        if ($loginlogs) {
            return response()->json([
                "code" => 200,
                "loginlogs" => $loginlogs
            ]);
        }

        return response()->json(["code" => 400]);
    }
    public function loadTenants()
    {
        $tenants = \DB::select(\DB::raw("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3"));
        if ($tenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $tenants
            ]);
        }
    }


    public function loadSubTenants($id)
    {
        //$subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)) from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '-'), '>', s.name), s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id) select id, name from cte order by path"));
        $subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '_') from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('_', 50), $id, '', CONCAT(id, '') from subtenant where parent_id = $id) select id, name from cte order by path"));
        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $subtenants
            ]);
        }
    }
    public function loadloginlogDataSector($id)
    {
        $loginlogsdata=[];
        $orgidval = \DB::select(\DB::raw("select id from subtenant where parent_id=$id"));
        $orgid =$orgidval;
        foreach($orgid as $org){

            $useridval = \DB::select(\DB::raw("select id from subtenant where parent_id=$org->id"));
            $user_ids = $useridval;
            foreach($user_ids as $user_id) {
                //echo $user_id->id."==";
                $loginlogs = \DB::table("login_activities")
                    ->join('users', 'login_activities.user_id', '=', 'users.id')
                    ->select('login_activities.*', 'users.name')
                    ->where('login_activities.user_id', '=', $user_id->id)
                    ->get();
                if(count($loginlogs)!=0){
                   $loginlogsdata[]=$loginlogs;
                }


            }
        }



        if ($loginlogsdata) {
            return response()->json([
                "code" => 200,
                "loginlogs" => $loginlogsdata
            ]);
        }

        return response()->json(["code" => 400,"loginlogs" => $loginlogsdata]);
    }
    public function loadloginlogDataOrgUnit($id)
    {
        $loginlogsdata=[];
            $useridval = \DB::select(\DB::raw("select id from subtenant where parent_id=id"));
            $user_ids = $useridval;
            foreach($user_ids as $user_id) {
                //echo $user_id->id."==";
                $loginlogs = \DB::table("login_activities")
                    ->join('users', 'login_activities.user_id', '=', 'users.id')
                    ->select('login_activities.*', 'users.name')
                    ->where('login_activities.user_id', '=', $user_id->id)
                    ->get();
                if(count($loginlogs)!=0){
                   $loginlogsdata[]=$loginlogs;
                }


            }




        if ($loginlogsdata) {
            return response()->json([
                "code" => 200,
                "loginlogs" => $loginlogsdata
            ]);
        }

        return response()->json(["code" => 400,"loginlogs" => $loginlogsdata]);
    }

    public function loadloginDataDate($data)
    {

        $str_arr = explode (",", $data);
        $str_arr[0]=str_replace(" GMT+0300 (Arabian Standard Time)", " ",$str_arr[0]);
        $str_arr[1]= str_replace(" GMT+0300 (Arabian Standard Time)", " ",$str_arr[1]);

         $dates=strtotime($str_arr[0]);
         //echo $dates;
         $datee=strtotime($str_arr[1]);

       $startdate= date('yy-m-d H:i:s', $dates);
       $enddate= date('yy-m-d H:i:s', $datee);
        $loginlogs = \DB::table("login_activities")
            ->join('users', 'login_activities.user_id', '=', 'users.id')
            ->select('login_activities.*','users.name')
            ->where('login_activities.created_at', '>=', $startdate)
            ->where('login_activities.created_at', '<=', $enddate)
            ->get();
        if ($loginlogs) {
            return response()->json([
                "code" => 200,
                "loginlogs" => $loginlogs
            ]);
        }
        return response()->json(["code" => 400,"loginlogs" => $loginlogs]);
    }
}

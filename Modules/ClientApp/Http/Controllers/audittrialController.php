<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class audittrialController extends Controller
{
    //
    public function index()
    {
        $audittrial = \DB::table("audits")
            ->join('users', 'audits.user_id', '=', 'users.id')
            ->select('audits.*','users.name','users.last_name','users.sector_id','users.subtenant_id')
           //->where('audits.created_at', '>=', date(Carbon::today()))
            ->get();
        $i=0;
        $j=1;

       // $input = "Current from 2014-10-10 to 2015/05/23 and 2001.02.10";
       // $output = preg_replace('/(\d{4}[\.\/\-][01]\d[\.\/\-][0-3]\d)/', '', $input);

        foreach($audittrial as $auditdata){
            $newvalues=$auditdata->new_values;
            $oldvalues=$auditdata->old_values;
            $auditable_type=$auditdata->auditable_type;

            $arrayaudittype = explode("\\",$auditable_type);
            $auditable_type = end($arrayaudittype);
            if($auditable_type=="Dynamic"){
                $auditable_type="KPI";
            }
            $sectorname = \DB::table("subtenant")
                ->select(\DB::raw('name'))
                ->where('subtenant.id', '=', $auditdata->sector_id)
                ->get();
            $sectorname = \DB::table("subtenant")
                ->select(\DB::raw('name'))
                ->where('subtenant.id', '=', $auditdata->sector_id)
                ->get();
            $orgunit= \DB::table("subtenant")
                ->select(\DB::raw('name'))
                ->where('subtenant.id', '=', $auditdata->subtenant_id)
                ->get();
            $audittrial[$i]->sectorname=$sectorname[0]->name;
            $audittrial[$i]->orgunit=$orgunit[0]->name;
          //echo $newvalues;
            //die();
            //$json = str_replace("\r\n", '\r\n', $newvalues); // single quotes do the trick



           // echo implode( ", ", $list );
            //echo $tags; // works
           // die();
            $audittrial[$i]->name=$auditdata->name." ".$auditdata->last_name;
            $newvalues=substr($newvalues, 1, -1);
            $oldvalues=substr($oldvalues, 1, -1);
            $newvalues=str_replace('"', " ", $newvalues);
            $oldvalues=str_replace('"', " ", $oldvalues);

            $arrayold=explode(',',$oldvalues);
            $arraynew=explode(',',$newvalues);

            $oldvalues= str_replace(end($arrayold), "", $oldvalues);
            $newvalues= str_replace(end($arraynew), "", $newvalues);

            $audittrial[$i]->new_valueslist=str_replace(",", "\n", $newvalues);
            $audittrial[$i]->old_valueslist=str_replace(",", "\n", $oldvalues);
            $audittrial[$i]->auditable_type=$auditable_type;
            $audittrial[$i]->no=$j;


            $i++;$j++;
        }
        //die();
       // die();
       // print_r($sectorname);
//        for($)
       //$audittrial[$i]['sectorname']=$sectorname;
        if ($audittrial) {
            return response()->json([
                "code" => 200,
                "audittrial" => $audittrial
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

    public function loadusers()
    {
        $users = \DB::select(\DB::raw("select id,name from users"));
        if ($users) {
            return response()->json([
                "code" => 200,
                "users" => $users
            ]);
        }
    }
    public function loadscreens()
    {
        $screens= \DB::select(\DB::raw("select distinct auditable_type as name from audits"));
        $i=0;
        foreach($screens as $screen){
            $auditable_type=$screen->name;

            $arrayaudittype = explode("\\",$auditable_type);
            $auditable_type = end($arrayaudittype);
            if($auditable_type=="Dynamic"){
                $auditable_type="KPI";
            }
            $screens[$i]->auditname=$auditable_type;
            $i++;
        }

        if ($screens) {
            return response()->json([
                "code" => 200,
                "screens" => $screens
            ]);
        }
    }

    public function audittrialfilter($value)
    {

        $valuearray=explode(",",$value);

        $sector=$valuearray[0];
        $orgunit=$valuearray[1];
        $users=$valuearray[2];
        $screen=$valuearray[3];
        $datetime1=$valuearray[4];
        if(count($valuearray)==6){
            $actions=$valuearray[5];
        }
        else{
            $datetime2=$valuearray[5];
            $actions=$valuearray[6];
        }
        $whereuser="";$wherescreen="";$whereactions="";$wheredate="";$whereorgunit="";$wheresector="";
       if(!empty($datetime1) && !empty($datetime2) ) {

           $datetime1 = str_replace(" GMT+0300 (Arabian Standard Time)", " ", $datetime1);
           $datetime2 = str_replace(" GMT+0300 (Arabian Standard Time)", " ", $datetime2);

           $dates = strtotime($datetime1);
           //echo $dates;
           $datee = strtotime($datetime2);

           $startdate = date('yy-m-d H:i:s', $dates);
           $enddate = date('yy-m-d H:i:s', $datee);
           $wheredate=" and  audits.created_at>='$startdate' and audits.created_at<='$enddate'";

       }

        if(!empty($sector))
        {
            // echo "in";
            $wheresector=" and  users.sector_id=$sector";
        }
        if(!empty($orgunit))
        {
            // echo "in";
            $whereorgunit=" and  users.subtenant_id=$orgunit";
        }

        if(!empty($users))
        {
           // echo "in";
            $whereuser=" and  audits.user_id=$users";
        }
        if(!empty($actions))
        {
            // echo "in";
            $whereactions=" and  audits.event='$actions'";
        }
        if(!empty($screen))
        {
            // echo "in";
            if($screen=="KPI"){
                $screen="Dynamic";
            }
            $wherescreen=" and audits.auditable_type like'%$screen%'";
        }
        $audittrial=\DB::select(\DB::raw("select `audits`.*, `users`.`name`, `users`.`last_name`, `users`.`sector_id`, `users`.`subtenant_id` from `audits` inner join `users` on `audits`.`user_id` = `users`.`id` where 1 $whereuser $wherescreen $whereactions $wheredate $whereorgunit $wheresector"));
        $i=0;
        $j=1;

        // $input = "Current from 2014-10-10 to 2015/05/23 and 2001.02.10";
        // $output = preg_replace('/(\d{4}[\.\/\-][01]\d[\.\/\-][0-3]\d)/', '', $input);


//print_r($user_idarray);

        //$array = json_decode(json_encode($user_id), true);

        foreach($audittrial as $auditdata) {

            $sector_id = \DB::table("users")
                ->select(\DB::raw('*'))
                ->where('users.id', '=', $auditdata->user_id)
                ->get();

           // if ($sector_id[0]->sector_id == $sector) {
                $newvalues = $auditdata->new_values;
                $oldvalues = $auditdata->old_values;
                $auditable_type = $auditdata->auditable_type;

                $arrayaudittype = explode("\\", $auditable_type);
                $auditable_type = end($arrayaudittype);
                if ($auditable_type == "Dynamic") {
                    $auditable_type = "KPI";
                }
                $sectorname = \DB::table("subtenant")
                    ->select(\DB::raw('name'))
                    ->where('subtenant.id', '=', $auditdata->sector_id)
                    ->get();
                $sectorname = \DB::table("subtenant")
                    ->select(\DB::raw('name'))
                    ->where('subtenant.id', '=', $auditdata->sector_id)
                    ->get();
                $orgunit = \DB::table("subtenant")
                    ->select(\DB::raw('name'))
                    ->where('subtenant.id', '=', $auditdata->subtenant_id)
                    ->get();
               ;
                $audittrial[$i]->sectorname = $sectorname[0]->name;
                $audittrial[$i]->orgunit = $orgunit[0]->name;
                //echo $newvalues;
                //die();
                //$json = str_replace("\r\n", '\r\n', $newvalues); // single quotes do the trick


                // echo implode( ", ", $list );
                //echo $tags; // works
                // die();
                $audittrial[$i]->name = $auditdata->name . " " . $auditdata->last_name;
                $newvalues = substr($newvalues, 1, -1);
                $oldvalues = substr($oldvalues, 1, -1);
                $newvalues = str_replace('"', " ", $newvalues);
                $oldvalues = str_replace('"', " ", $oldvalues);

                $arrayold = explode(',', $oldvalues);
                $arraynew = explode(',', $newvalues);

                $oldvalues = str_replace(end($arrayold), "", $oldvalues);
                $newvalues = str_replace(end($arraynew), "", $newvalues);

                $audittrial[$i]->new_valueslist = str_replace(",", "\n", $newvalues);
                $audittrial[$i]->old_valueslist = str_replace(",", "\n", $oldvalues);
                $audittrial[$i]->auditable_type = $auditable_type;
                $audittrial[$i]->no = $j;


                $i++;
                $j++;
          //  } else {
//                $audittrial = '';

          //  }
        }
            if ($audittrial) {
                return response()->json([
                    "code" => 200,
                    "audittrial" => $audittrial
                ]);
            }

            return response()->json(["code" => 400]);


       // echo $sector."=".$orgunit."=".$users."=".$screen."=".$datetime;
        //die();
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

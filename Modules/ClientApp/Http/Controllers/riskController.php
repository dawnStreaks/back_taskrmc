<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Maher\Counters\Facades\Counters;
use Maher\Counters\Models\Counter;
use Modules\ClientApp\Dynamic;
use Modules\ClientApp\Entities\Projects;
use DateTime;
use Spatie\Permission\Models\Role;





class riskController extends Controller
{
    //
    public function index()
    {



    }
    public function loadTenantsministry()
    {
        $tenants = \DB::select(\DB::raw("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3 || s.subtenant_type_id=2"));
        if ($tenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $tenants
            ]);
        }
    }

    public function risklist(Request $request)
    {
        $risklist =\DB::select(\DB::raw("select p.id,p.project_id,p.description,p.risk_trigger,p.risk_cat,p.risk_owner,p.riks_owner_ext,p.impact,p.propability,(p.impact*p.propability)as priority,p.treatment,p.status,p.identified_dt,p.end_dt,p.action_plan,c.sector_id,c.subtenant_id,c.created_by from proj_risks p inner join project c on p.project_id = c.id"));


            return response()->json([
                "code" => 200,
                "risklist" => $risklist
            ]);
        
    }

    public function userlist(Request $request)
    {
        $risklist =\DB::select(\DB::raw("select id,name from users"));


            return response()->json([
                "code" => 200,
                "data" => $risklist
            ]);
        
    }

    public function riskcatlist(Request $request)
    {
        $risklist =\DB::select(\DB::raw("select id,name from proj_risk_cat"));


            return response()->json([
                "code" => 200,
                "data" => $risklist
            ]);
        
    }

    public function loadprojectearch($value)
{

    $valuearray=explode(",",$value);

    $sector=$valuearray[0];
    $orgunit=$valuearray[1];
    // $projectcat=$valuearray[2];
    // $projectop=$valuearray[3];


    $wherecat="";$whereop="";$whereorgunit="";$wheresector="";


    if(!empty($sector))
    {
        // echo "in";
        $wheresector=" and  project.sector_id=$sector";
    }
    if(!empty($orgunit))
    {
        // echo "in";
        $whereorgunit=" and  project.subtenant_id=$orgunit";
    }
    
    $projectlist=\DB::select(\DB::raw("select project.id, project.name from project where 1 $whereorgunit $wheresector"));
   
        return response()->json([
            "code" => 200,
            "projectlist" => $projectlist
        ]);
    // }

    // return response()->json(["code" => 400]);


    // echo $sector."=".$orgunit."=".$users."=".$screen."=".$datetime;
    //die();
}

    public function getprojecttypes()
    {
        $project_types = \DB::select(\DB::raw("select * from proj_type"));
        if ($project_types) {
            return response()->json([
                "code" => 200,
                "project_types" => $project_types
            ]);
        }
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

    public function store(Request $request)
    {
        // $date_identified=new DateTime($request->date_identified);
        // $date_closed=new DateTime($request->closure_date);
        // $dt_identified=$date_identified->format('d/m/Y');
        // $dt_end=$date_closed->format('d/m/Y');
        $strategy_info =  \DB::table("project")
        ->select('id','name','tenant_id')
        ->where('id',$request->project_id)
        ->first();

//         $id_info =  \DB::table("proj_risk_cat")
//         ->select('id')
//         ->where('id',$strategy_info->id)
//         ->first();

// if(!$id_info)
//         $riskdef = \DB::table("proj_risk_cat")->insert(
//             [
//                 'id' =>$strategy_info->id,
//                 'tenant_id' => $strategy_info->tenant_id,
//                 'name' =>$strategy_info->name,
//                 ]
//             );

        
        $date_hol1 = strtotime("$request->date_identified");
            $dt_identified = date("d-m-y", $date_hol1);
if($request->closure_date!=NULL){
            $date_hol2 = strtotime("$request->closure_date");
            $dt_end = date("d-m-y", $date_hol2);
}
else{
    $dt_end =$request->closure_date;
}

        $riskdef = \DB::table("proj_risks")->insertGetId(//insert(
            [
                'project_id' => $request->project_id,
                'description' => $request->risk_description,
                'risk_cat' =>$request->risk_category,
                'risk_trigger'=> $request->risk_trigger,
                'status' =>$request->risk_status,
                'end_dt'=>$dt_end,
                'risk_owner' => $request->risk_owner,
                'riks_owner_ext' => $request->risk_owner_ext,
                'propability' =>$request->propability,
                'impact'=> $request->impact,
                'treatment' =>$request->risk_treatment,
                'identified_dt'=>$dt_identified,
                'action_plan'=> $request->action_plan,

            ]
        );

        if ($riskdef) {
            return response()->json([
                "code" => 200,
                "msg" => "data inserted successfully"
            ]);
        }

        return response()->json(["code" => 400]);
    }

    public function show($id)
    {
        // var_dump($id);
        //$user_info = Leaves::where("id", $id)->first();
        $strategy_info =  \DB::table("proj_risks")
        ->select('id','description','risk_trigger','risk_cat','risk_owner','riks_owner_ext','propability','impact','treatment','status','identified_dt','end_dt','action_plan')
        ->where('id',$id)
        ->first();

        $strategy = [];
        if ($strategy_info) {
            /*echo $process_info->date_from;
            die;*/

            $strategy['id'] = $strategy_info->id;
            $strategy['risk_description'] = $strategy_info->description;
            $strategy['risk_trigger'] = $strategy_info->risk_trigger;
            $strategy['risk_cat'] = $strategy_info->risk_cat;
            $strategy['risk_owner'] = $strategy_info->risk_owner;
            $strategy['risk_owner_ext'] = $strategy_info->riks_owner_ext;
            $strategy['propability'] = $strategy_info->propability;
            $strategy['impact'] = $strategy_info->impact;
            $strategy['priority'] = ($strategy_info->impact)*($strategy_info->propability);
            $strategy['risk_treatment'] = $strategy_info->treatment;
            $strategy['risk_status'] = $strategy_info->status;
            $strategy['date_identified'] = $strategy_info->identified_dt;
            $strategy['closure_date'] = $strategy_info->end_dt;
            $strategy['action_plan'] = $strategy_info->action_plan;


            


            return response()->json([
                "code" => 200,
                "data" => $strategy,
            ]);
        } else {
            return response()->json([
                "code" => 201,
                // "msg" => "غير موجود"
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $date_hol1 = strtotime("$request->date_identified");
            $dt_identified = date("y-m-d", $date_hol1);
if($request->closure_date!=NULL){
            $date_hol2 = strtotime("$request->closure_date");
            $dt_end = date("y-m-d", $date_hol2);
}
else{
    $dt_end =$request->closure_date;
}
// var_dump($request);

        $prc_update = \DB::table("proj_risks")
        ->where('id', $id)
        ->update([
            'description' => $request->risk_description,
            'risk_trigger' => $request->risk_trigger,
            'risk_cat' => $request->risk_cat,
            'risk_owner' => $request->risk_owner,
            'riks_owner_ext' => $request->risk_owner_ext,
            'impact' => $request->impact,
            'propability' => $request->propability,
            'treatment' => $request->risk_treatment,
            'status' => $request->risk_status,
            'identified_dt' =>$dt_identified,
            'end_dt' =>$dt_end,
            'action_plan'=>$request->action_plan,
                   
        ]);

       
            if($prc_update) {
                // $prc_update->save();

                return response()->json([
                    "code" => 200,
                    // "msg" =>'updated'// 'تم تعديل طلب الإجازة'
                ]);

            }
            else{
                return response()->json([
                    "code" => 201,
                    // "msg" => 'not updated'//'لا يمكن الحفظ لأن البيانات تم تعديلها من قبل مستخدم آخر, رجاء إعادة فتح الصفحة'
                ]);

            }
        }
       
    

    public function destroy($id)

    {   
        $prctype = \DB::table("proj_risks")->Where('id', $id)->delete();
        // $prctype = \DB::table("objective")->Where('id', $id)->delete();
// var_dump($id);
        if (!$prctype) {
            return response()->json([
                "code" => 404,
                // "msg" => "not deleted"//"لا يمكن حذف طلب الإجازة"
            ]);
        }

            // $prctype->delete();

            return response()->json([
                "code" => 200,
                // "msg" =>"deleted" //"تم حذف طلب الإجازة"
            ]);


    }

    public function subtenanttree($id)
    {

        $tenants1 = SubTenant::with('tree')->Where('parent_id', '<>',0)->Where('parent_id', '<>',1)->Where('parent_id', '<>',2)->Where('parent_id', '<>',3)->Where('parent_id','<>', null)->whereNotNull('id')->Where('parent_id', $id)->get();


        $tenants = SubTenant::with('children')->orWhere('parent_id', $id)->get();
        $i=0;$j=0;
        if ($tenants) {

            foreach($tenants as $tenant){
                $tenants[$i]['id']=$tenant->id;
                $tenants[$i]['label']=$tenant->name;

                $i=$i+1;
            }
            $j=0;
//            foreach ( $tenants1 as $k=>$v )
//            {
//                $tenants1[$k] ['tree'] = $tenants1[$k] ['children'];
//                unset($tenants1[$k]['tree']);
//            }
//            if ($tenants1) {

//                foreach($tenants1 as $tenant1){
//                    $tenants1[$j]['id']=$tenant1->id;
//                    $tenants1[$j]['label']=$tenant1->name;
//                   // $tenantsarray[][$j]['label']= $tenants1[$j]['label'];
//                    $tenants1[$j]['children']=$tenant1->tree;
////                    unset($tenants1[$j]['tree']);
//
//
//                    // $tenantsarray[][]['children']=$tenants1[$j]['children'];
//
//                    $j=$j+1;
//                }
//            }
//var_dump($tenantsarray);
//            die();
            $j=0;
            foreach($tenants1 as $tenant12){
               $tenants1[$j]['id']=$tenant12->id;
               $tenants1[$j]['label']=$tenant12->name;

               $j=$j+1;
            }
//            $tenants1 = array_map('array_filter', $tenants1);
//            $tenants1 = array_filter($tenants1);
        //    $tenants1=array_filter($tenants1);

            return response()->json([
                "code" => 200,
                "subTenants" => $tenants1,
                "subTenantsdept" => $tenants
            ]);
        }

        return response()->json(["code" => 400]);
    }



    public function processList(array $list)
    {
        $listResult = ['keepValue' => false, // once set true will propagate upward
            'value'       => []];

        foreach ($list as $name => $item ) {
            if (is_null($item)) { // see is_scalar test
                continue;
            }

            if (is_scalar($item)) { // keep the value?
                if (!empty($item) || strlen(trim($item)) > 0) {
                    $listResult['keepValue'] = true;
                    $listResult['value'][$name] = $item;
                }

            } else { // new list... recurse

                $itemResult = processList($item);
                if ($itemResult['keepValue']) {
                    $listResult['keepValue'] = true;
                    $listResult['value'][$name] = $itemResult['value'];
                }
            }
        }
        return $listResult;
    }

    public function loadKpiOrgUsersNotification($orgUnit, $sector =null) {
        if($orgUnit != 'null') {

            $users = \DB::select(\DB::raw("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
		from subtenant where
        id = $orgUnit -- set your arg here
	UNION ALL
    -- This is the recursive part: It joins to cte
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
		from subtenant s
        inner join cte c on s.parent_id = c.id
	)
	-- select id, name, subtenant_type, parent_id
	--  cte.level1_id, cte.id, cte.parent_id, cte.subtenant_type,
	select cte.name as subname, u.*
	from cte, users u where
	u.subtenant_id = cte.id
	order by path, u.name;"));
        } else {
            $users = \DB::table('users')
                ->join('subtenant', 'subtenant.id', '=', 'users.subtenant_id')
                ->select('users.*', 'subtenant.name as subname')
                ->where('users.tenant_id' , $this->user->tenant_id)
                ->where('users.sector_id', '=', $sector)
                ->where('users.deleted_at' , null)
                ->orderBy('users.id', 'desc');
            $users = $users->distinct()->get();
        }
        return response()->json([
            "code" => 200,
            "data" => $users
        ]);
    }

    function transformTree($treeArrayGroups, $rootArray)
    {
        // Read through all nodes where parent is root array
        foreach ($treeArrayGroups[$rootArray['id']] as $child) {
            //echo $child['id'].PHP_EOL;
            // If there is a group for that child, aka the child has children
            if (isset($treeArrayGroups[$child['id']])) {
                // Traverse into the child
                $newChild = $this->transformTree($treeArrayGroups, $child);
            } else {
                $newChild = $child;
            }

            if($child['id'] != '') {
                // Assign the child to the array of children in the root node
                $rootArray['tree'][] = $newChild;
            }
        }
        return $rootArray;
    }

    public function loadNotificationDefaultData(Request $request)
    {
        $notiArgs = \DB::table('notif_arg')->get();
        $notiEvent = \DB::table('notif_event')->get();
        $roles = Role::get();
        $dom = [];
        for ($i = 1; $i <= 31; $i++) {
            $dom[] = $i;
        }

        $rows = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '') from subtenant where id = 2 UNION ALL select s.id, concat(CONCAT(c.level, ''), '', s.name), s.parent_id, CONCAT(c.level, ''), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('', 50), 2, '', CONCAT(id, '') from subtenant where id = 2) select id, name as label, name, parent_id from cte order by path"));
        $result = array_map(function ($value) {
            return (array)$value;
        }, $rows);

        // Group by parent id
        $treeArrayGroups = [];
        foreach ($result as $record) {
            $treeArrayGroups[$record['parent_id']][] = $record;
        }
        // Get the root
        $rootArray = $result[0]['id'] != '' ? $result[0] : $result[1];
        // Transform the data
        $outputTree = $this->transformTree($treeArrayGroups, $rootArray);

        $data = [];
        $data[] = $outputTree;

        return response()->json([
            "code" => 200,
            "roles" => $roles,
            "notiArgs" => $notiArgs,
            "notiEvent" => $notiEvent,
            "dom" => $dom,
            "sectors" => $data
        ]);
    }

}

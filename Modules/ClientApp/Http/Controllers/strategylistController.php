<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class strategylistController extends Controller
{
    //
    public function index()
    {
        $strategylist11 = \DB::table("objective")
        ->select('id','name','objective_type','tenant_id')
        ->get();
        foreach( $strategylist11 as  $strategylist1) {

        $cat_id=$strategylist1->objective_type;
        $tenant_id= $strategylist1->tenant_id ;

if($cat_id!=NULL)
{
    $a=array("Vision"=>"v","Goal"=>"g","Target"=>"t");

    $cat_name= array_search($cat_id,$a);//\DB::select(\DB::raw("select name from objective_type where id=$cat_id"));

  
    if(isset($cat_name)){
        // var_dump($cat_name[0]->name);

        $strategylist1->category_name=$cat_name;
    }

}
else{
   
    $strategylist1->category_name=NULL;

}
    // $strategylist1->sector_name=$sectorname;
      
    $tenant_name= \DB::select(\DB::raw("select name from tenant where id=$tenant_id"));
    $strategylist1->tenant_name=$tenant_name[0]->name;

}
    if ($strategylist11) {
        return response()->json([
            "code" => 200,
            "data" => $strategylist11
        ]);
    }
   
    }


    public function StrategyListTableSector(Request $request)
    {
        $parent_id = $request->parent;
        $strategylist11 = \DB::table("objective")
        ->join('org_objectives', 'org_objectives.objective_id', '=', 'objective.id')
        ->select('objective.id','objective.name','objective.objective_type','objective.tenant_id')
        ->where('org_objectives.sector_id',$parent_id)
        ->get();
        foreach( $strategylist11 as  $strategylist1) {

            $cat_id=$strategylist1->objective_type;
            $tenant_id= $strategylist1->tenant_id ;
    
    if($cat_id!=NULL)
    {
        $a=array("Vision"=>"v","Goal"=>"g","Target"=>"t");
    
        $cat_name= array_search($cat_id,$a);//\DB::select(\DB::raw("select name from objective_type where id=$cat_id"));
    
      
        if(isset($cat_name)){
            // var_dump($cat_name[0]->name);
    
            $strategylist1->category_name=$cat_name;
        }
    
    }
    else{
       
        $strategylist1->category_name=NULL;
    
    }
        // $strategylist1->sector_name=$sectorname;
          
        $tenant_name= \DB::select(\DB::raw("select name from tenant where id=$tenant_id"));
        $strategylist1->tenant_name=$tenant_name[0]->name;
    
    }
        
    if ($strategylist11) {
        return response()->json([
            "code" => 200,
            "data" => $strategylist11
        ]);
    }
   
    }


    public function StrategyListTableOrg(Request $request)
    {
        $parent_id = $request->parent;
        $strategylist11 = \DB::table("objective")
        ->join('org_objectives', 'org_objectives.objective_id', '=', 'objective.id')
        ->select('objective.id','objective.name','objective.objective_type','objective.tenant_id')
        ->where('org_objectives.subtenant_id',$parent_id)
        ->get();
        foreach( $strategylist11 as  $strategylist1) {

            $cat_id=$strategylist1->objective_type;
            $tenant_id= $strategylist1->tenant_id ;
            // var_dump($tenant_id);
    
    if($cat_id!=NULL)
    {
        $a=array("Vision"=>"v","Goal"=>"g","Target"=>"t");
    
        $cat_name= array_search($cat_id,$a);//\DB::select(\DB::raw("select name from objective_type where id=$cat_id"));
    
      
        if(isset($cat_name)){
            // var_dump($cat_name[0]->name);
    
            $strategylist1->category_name=$cat_name;
        }
    
    }
    else{
       
        $strategylist1->category_name=NULL;
    
    }
        // $strategylist1->sector_name=$sectorname;
          
        $tenant_name= \DB::select(\DB::raw("select name from tenant where id=$tenant_id"));
        $strategylist1->tenant_name=$tenant_name[0]->name;
    
    }
    if ($strategylist11) {
        return response()->json([
            "code" => 200,
            "data" => $strategylist11
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


    public function loadlinkSubTenants($id)
    {
        //  $sector_id= collect(\DB::select(\DB::raw("select sector_id  from strategy_subtenant_rel where strategy_id=$id;")));
               
        // $sid=$sector_id[0]->sector_id;
        // $sector_name= \DB::select(\DB::raw("select name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3 and s.id=$sid"));

        $subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '_') from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('_', 50), $id, '', CONCAT(id, '') from subtenant where parent_id = $id) select id, name from cte order by path"));
        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $subtenants
            ]);
        }
    }


    public function loadlinkTenants()
    {
         $tenants = \DB::select(\DB::raw("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3"));
         if ($tenants) {
             return response()->json([
                 "code" => 200,
                 "tenants" => $tenants
             ]);
         }
    }


    public function loadlinkTenantsreal()
    {
        $id=env('TENANT_ID');
         $tenants = \DB::select(\DB::raw("select id, name from tenant where id=$id"));
         if ($tenants) {
             return response()->json([
                 "code" => 200,
                 "tenants" => $tenants
             ]);
         }
    }


    public function loadCategory()
    {
        // $a=array("Vision"=>"v","Goal"=>"g","Target"=>"t");

        // $cat_name= array_search($cat_id,$a);//\DB::select(\DB::raw("select name from objective_type where id=$cat_id"));
        $kpiCat = array(
            array(
                "id" => "v",
                "name" => "Vision",
                "name_ar"=>"رؤية"
            ),
            array(
                "id" =>"g",
                "name" => "Goal",
                "name_ar"=>"هدف"
            ),
            array(
                "id" => "t",
                "name" => "Target",
                "name_ar"=>"غاية"
            )
        );
        // $kpiCat = \DB::select(\DB::raw("select id, name from objective_type"));
        if ($kpiCat) {
            return response()->json([
                "code" => 200,
                "kpiCat" => $kpiCat
            ]);
        }
    }

    public function store(Request $request)
    {

        $user_info = collect(\DB::select(\DB::raw("select 1, max(cast(id as integer))+1 as id from objective;")))->first();
        // $id = \DB::table("process_def")->select(\DB::raw('MAX(cast(id as integer))'));
        $id= $user_info->id;
        // if (!$user_info) {
            // var_dump($id);
            $prctype = \DB::table("objective")->insert(//insert(
                [
                    'id'=>$id,
                    'name' => $request->StrategyName,
                    'objective_type' => $request->Category,
                    'tenant_id' => env('TENANT_ID'),//1,
                    'parent_id' => NULL,


                ]
            );

            if ($prctype) {
                return response()->json([
                    "code" => 200,
                    // "msg" => "تم إنشاء طلب الإجازة"
                ]);
            }

         else {
            return response()->json([
                "code" => 201,
                // "msg" => "تم إنشاء طلب الإجازة"
            ]);
        
    }

        }    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // var_dump($id);
        //$user_info = Leaves::where("id", $id)->first();
        $strategy_info =  \DB::table("objective")
        ->select('id','name','objective_type','tenant_id')
        ->where('id',$id)
        ->first();

        $strategy = [];
        if ($strategy_info) {
            /*echo $process_info->date_from;
            die;*/
            $strategy['id'] = $strategy_info->id;
            $strategy['StrategyName'] = $strategy_info->name;
            $strategy['Category'] = $strategy_info->objective_type;
            $strategy['Tenant'] = $strategy_info->tenant_id;
            


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

        $prc_update = \DB::table("objective")
        ->where('id', $id)
        ->update([
            'name' => $request->StrategyName,
            'objective_type' => $request->Category,

        
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
        $prctype1 = \DB::table("org_objectives")->Where('objective_id', $id)->delete();
        $prctype = \DB::table("objective")->Where('id', $id)->delete();

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

//link part

public function linkedlist(Request $request)
{   
    $prc_id = $request->prc_id;
    $strategylist11 = \DB::table("org_objectives")
    ->select('org_objectives.tenant_id','org_objectives.sector_id','org_objectives.subtenant_id')
    ->where('org_objectives.objective_id', $prc_id)
    ->get();
   if($strategylist11){
    foreach( $strategylist11 as  $strategylist1) {
    $subtenant_id= $strategylist1->subtenant_id ;
    $sector_id= $strategylist1->sector_id ;
    $tenant_id= $strategylist1->tenant_id ;

   if($subtenant_id!=NULL){
    $sub_name= \DB::select(\DB::raw("select name  from subtenant where id=$subtenant_id;"));
   }

  if($sector_id!=null)
    $sector_name = \DB::select(\DB::raw("select name from subtenant s where s.tenant_id=$tenant_id and id=$sector_id"));
    
    $tenant_name = \DB::select(\DB::raw("select name from tenant s where s.id=$tenant_id"));
  
    $subtenantname=$subtenant_id!=null?$sub_name[0]->name:"";
    $sectorname=$sector_id!=null?$sector_name[0]->name:"";
    $tenantname=$tenant_name[0]->name;
if($subtenant_id!=$sector_id)
{
    $strategylist1->subtenant_name=$subtenantname;
    $strategylist1->sector_name=$sectorname;
    $strategylist1->tenant_name=$tenantname;

}    
if($subtenant_id==$sector_id)
{
    $strategylist1->subtenant_name="";
    $strategylist1->sector_name=$sectorname;
    $strategylist1->tenant_name=$tenantname;

}    
}
}
if ($strategylist11) {
    return response()->json([
        "code" => 200,
        "data" => $strategylist11
    ]);
}
if (!$strategylist11) {
    return response()->json([
        "code" => 200,
        "data" => []
    ]);
}


}


    public function store1(Request $request)
    {

        $user_info = collect(\DB::select(\DB::raw("select 1, max(cast(id as integer))+1 as id from objective;")))->first();
        $id= $user_info->id;
            $prctype = \DB::table("objective")->insert(//insert(
                [
                    'id'=>$id,
                    'name' => $request->StrategyName,
                    'objective_type' => $request->Category,
                    'tenant_id' => env('TENANT_ID'),//1,
                    'parent_id' => NULL,


                ]
            );

            if ($prctype) {
                return response()->json([
                    "code" => 200,
                    // "msg" => "تم إنشاء طلب الإجازة"
                ]);
            }

         else {
            return response()->json([
                "code" => 201,
                // "msg" => "تم إنشاء طلب الإجازة"
            ]);
        
    }

        } 


        public function update1(Request $request, $id)
        {
            $category_id= collect(\DB::select(\DB::raw("select objective_type  from objective where id=$id;")));
               
             $cid=$category_id[0]->objective_type;
    // var_dump($cid);
    if($cid=="v")
            $prctype = \DB::table("org_objectives")->insertGetId(//insert(
                [
                    'tenant_id' => env('TENANT_ID'),
                    'sector_id' => null,
                    'subtenant_id' =>null,
                    'objective_id'=> $id,
                



                ]
            );
            if($cid=="t")
            $prctype = \DB::table("org_objectives")->insertGetId(//insert(
                [
                    'tenant_id' => env('TENANT_ID'),
                    'sector_id' => $request->linkSector,
                    'subtenant_id' =>$request->linkSector,
                    'objective_id'=> $id,
                



                ]
            );
            if($cid=="g")
            $prctype = \DB::table("org_objectives")->insertGetId(//insert(
                [
                    'tenant_id' => env('TENANT_ID'),
                    'sector_id' => $request->linkSector,
                    'subtenant_id' =>$request->linkOrg,
                    'objective_id'=> $id,
                



                ]
            );

            if ($prctype) {
                return response()->json([
                    "code" => 200,
                    // "msg" => "تم إنشاء طلب الإجازة"
                ]);
            }

         else {
            return response()->json([
                "code" => 201,
                // "msg" => "تم إنشاء طلب الإجازة"
            ]);

            }
           
        }
    
        public function destroy1(Request $request)
    
        {   
            
            $id=$request->linkId;
            $sid=$request->linkSector;
            // echo $sid;
            $tid=$request->linkTenant;

            $oid=$request->linkOrg;

            // $category_id= collect(\DB::select(\DB::raw("select objective_type  from objective where id=$id;")));
               
            // $cid=$category_id[0]->objective_type;
            // var_dump($oid);

   if($sid=="null" && $oid=="null")
   {
            // var_dump($oid);

   $prctype = \DB::table("org_objectives")->Where('tenant_id', $tid)->Where('objective_id', $id)->whereNull('sector_id')->whereNull('subtenant_id')->delete();
//    return response()->json([
//     "code" => 200,
//     // "msg" => "تم حذف طلب الإجازة"
// ]);

//    $prctype->delete();  
}

   else if($sid!="null" && $oid=="null")
   
   $prctype = \DB::table("org_objectives")->Where('sector_id', $sid)->Where('tenant_id', $tid)->Where('objective_id', $id)->whereNull('subtenant_id')->delete();

  else if($sid!="null" && $oid!="null")
   $prctype = \DB::table("org_objectives")->Where('sector_id', $sid)->Where('subtenant_id', $oid)->Where('tenant_id', $tid)->Where('objective_id', $id)->delete();

            // $prctype = \DB::table("process_def")->Where('id', $id)->delete();
            // var_dump($sid.$tid.$oid);

            if (!$prctype) {
                return response()->json([
                    "code" => 404,
                    // "msg" => "لا يمكن حذف طلب الإجازة"
                ]);
            }
        
    
    else{
                    return response()->json([
                        "code" => 200,
                        // "msg" => "تم حذف طلب الإجازة"
                    ]);
        
    
        }
    }
    

}

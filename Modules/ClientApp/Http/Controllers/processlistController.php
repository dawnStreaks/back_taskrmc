<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class processlistController extends Controller
{
    //
    public function index()
    {
        $processlist11 = \DB::table("process_def")
        ->select('id','name','process_cat','tenant_id')
        ->get();
        foreach( $processlist11 as  $processlist1) {

        $cat_id=$processlist1->process_cat;
        $tenant_id= $processlist1->tenant_id ;

if($cat_id!=NULL)
{
    $cat_name= \DB::select(\DB::raw("select name from process_cat where id=$cat_id"));

  
    if(isset($cat_name[0]->name)){
        // var_dump($cat_name[0]->name);

        $processlist1->category_name=$cat_name[0]->name;
    }

}
else{
   
    $processlist1->category_name=NULL;

}
    // $processlist1->sector_name=$sectorname;
      
    $tenant_name= \DB::select(\DB::raw("select name from tenant where id=$tenant_id"));
    $processlist1->tenant_name=$tenant_name[0]->name;

}
    if ($processlist11) {
        return response()->json([
            "code" => 200,
            "data" => $processlist11
        ]);
    }
   
    }


    public function ProcessListTableSector(Request $request)
    {
        $parent_id = $request->parent;
        $processlist11 = \DB::table("process_def")
        ->join('process_subtenant_rel', 'process_subtenant_rel.process_id', '=', 'process_def.id')
        ->select('process_def.id','process_def.name','process_def.process_cat','process_def.tenant_id')
        ->where('process_subtenant_rel.sector_id',$parent_id)
        ->get();
        foreach( $processlist11 as  $processlist1) {

            $cat_id=$processlist1->process_cat;
            $tenant_id= $processlist1->tenant_id ;
    
    if($cat_id!=NULL)
    {
        $cat_name= \DB::select(\DB::raw("select name from process_cat where id=$cat_id"));
    
      
        if(isset($cat_name[0]->name)){
            // var_dump($cat_name[0]->name);
    
            $processlist1->category_name=$cat_name[0]->name;
        }
    
    }
    else{
       
        $processlist1->category_name=NULL;
    
    }
        // $processlist1->sector_name=$sectorname;
          
        $tenant_name= \DB::select(\DB::raw("select name from tenant where id=$tenant_id"));
        $processlist1->tenant_name=$tenant_name[0]->name;
    
    }
    if ($processlist11) {
        return response()->json([
            "code" => 200,
            "data" => $processlist11
        ]);
    }
   
    }


    public function ProcessListTableOrg(Request $request)
    {
        $parent_id = $request->parent;
        $processlist11 = \DB::table("process_def")
        ->join('process_subtenant_rel', 'process_subtenant_rel.process_id', '=', 'process_def.id')
        ->select('process_def.id','process_def.name','process_def.process_cat','process_def.tenant_id')
        ->where('process_subtenant_rel.subtenant_id',$parent_id)
        ->get();
        foreach( $processlist11 as  $processlist1) {

            $cat_id=$processlist1->process_cat;
            $tenant_id= $processlist1->tenant_id ;
    
    if($cat_id!=NULL)
    {
        $cat_name= \DB::select(\DB::raw("select name from process_cat where id=$cat_id"));
    
      
        if(isset($cat_name[0]->name)){
            // var_dump($cat_name[0]->name);
    
            $processlist1->category_name=$cat_name[0]->name;
        }
    
    }
    else{
       
        $processlist1->category_name=NULL;
    
    }
        // $processlist1->sector_name=$sectorname;
          
        $tenant_name= \DB::select(\DB::raw("select name from tenant where id=$tenant_id"));
        $processlist1->tenant_name=$tenant_name[0]->name;
    
    }
    if ($processlist11) {
        return response()->json([
            "code" => 200,
            "data" => $processlist11
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
        //  $sector_id= collect(\DB::select(\DB::raw("select sector_id  from process_subtenant_rel where process_id=$id;")));
               
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


    public function loadCategory()
    {
        $kpiCat = \DB::select(\DB::raw("select id, name from process_cat"));
        if ($kpiCat) {
            return response()->json([
                "code" => 200,
                "kpiCat" => $kpiCat
            ]);
        }
    }

    public function store(Request $request)
    {

        $user_info = collect(\DB::select(\DB::raw("select 1, max(cast(id as integer))+1 as id from process_def;")))->first();
        // $id = \DB::table("process_def")->select(\DB::raw('MAX(cast(id as integer))'));
        $id= $user_info->id;
        // if (!$user_info) {
            // var_dump($id);
            $prctype = \DB::table("process_def")->insert(//insert(
                [
                    'id'=>$id,
                    'name' => $request->ProcessName,
                    'process_cat' => $request->Category,
                    'tenant_id' => env('TENANT_ID'),//1,
                    'key_' => $request->ProcessName,
                    'version' => 1,
                    'default_distr_type' => NULL,
                    'process_type' => 1,


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
        $process_info =  \DB::table("process_def")
        ->select('id','name','process_cat','tenant_id')
        ->where('id',$id)
        ->first();

        $process = [];
        if ($process_info) {
            /*echo $process_info->date_from;
            die;*/
            $process['id'] = $process_info->id;
            $process['ProcessName'] = $process_info->name;
            $process['Category'] = $process_info->process_cat;
            $process['Tenant'] = $process_info->tenant_id;
            


            return response()->json([
                "code" => 200,
                "data" => $process,
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

        $prc_update = \DB::table("process_def")
        ->where('id', $id)
        ->update([
            'name' => $request->ProcessName,
            'process_cat' => $request->Category,

        
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
        $prctype1 = \DB::table("process_subtenant_rel")->Where('process_id', $id)->delete();
        $prctype = \DB::table("process_def")->Where('id', $id)->delete();

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
    $processlist11 = \DB::table("process_subtenant_rel")
    ->select('process_subtenant_rel.sector_id','process_subtenant_rel.subtenant_id')
    ->where('process_subtenant_rel.process_id', $prc_id)
    ->get();
    foreach( $processlist11 as  $processlist1) {
    $subtenant_id= $processlist1->subtenant_id ;
    $sector_id= $processlist1->sector_id ;


    $sub_name= \DB::select(\DB::raw("select name  from subtenant where id=$subtenant_id and subtenant_type_id!=3;"));
    $sector_name = \DB::select(\DB::raw("select name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3 and id=$sector_id"));
              
    $subtenantname=$sub_name[0]->name;
    $sectorname=$sector_name[0]->name;

    $processlist1->subtenant_name=$subtenantname;
    $processlist1->sector_name=$sectorname;
    }
if ($processlist11) {
    return response()->json([
        "code" => 200,
        "data" => $processlist11
    ]);
}


}


    public function store1(Request $request)
    {

        $user_info = collect(\DB::select(\DB::raw("select 1, max(cast(id as integer))+1 as id from process_def;")))->first();
        $id= $user_info->id;
            $prctype = \DB::table("process_def")->insert(//insert(
                [
                    'id'=>$id,
                    'name' => $request->ProcessName,
                    'process_cat' => $request->Category,
                    'tenant_id' => env('TENANT_ID'),//1,
                    'key_' => $request->ProcessName,
                    'version' => 1,
                    'default_distr_type' => NULL,
                    'process_type' => 1,


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
            // $sector_id= collect(\DB::select(\DB::raw("select sector_id  from process_subtenant_rel where process_id=$id;")));
               
            // $sid=$sector_id[0]->sector_id;
    
    
            $prctype = \DB::table("process_subtenant_rel")->insertGetId(//insert(
                [
                   
                    'sector_id' => $request->linkSector,
                    'subtenant_id' =>$request->linkOrg,
                    'process_id'=> $id,
                    'effective_from'=> null,
                    'effective_to'=>null
                



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
            $sid=$request->linkSector;
            // echo $sid;
            $oid=$request->linkOrg;
            $prctype = \DB::table("process_subtenant_rel")->Where('sector_id', $sid)->Where('subtenant_id', $oid)->delete();
            // $prctype = \DB::table("process_def")->Where('id', $id)->delete();
    
            if (!$prctype) {
                return response()->json([
                    "code" => 404,
                    // "msg" => "لا يمكن حذف طلب الإجازة"
                ]);
            }
    
    
                return response()->json([
                    "code" => 200,
                    // "msg" => "تم حذف طلب الإجازة"
                ]);
    
    
        }
    

}

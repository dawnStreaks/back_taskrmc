<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class processcategoryController extends Controller
{
    //
    public function index(Request $request)
    {
        // $parent_id = $request->parent;
// var_dump($parent_id);
        $processlist1 = \DB::table("process_cat")
        // ->join('process_subtenant_rel', 'process_subtenant_rel.process_id', '=', 'process_cat.id')
        ->select('process_cat.id','process_cat.name','process_cat.tenant_id')
        // ->where('process_subtenant_rel.sector_id', '==', $parent_id)
        // ->where('process_subtenant_rel.subtenant_id', '==', $parent_id)
        // ->orwhere(")

        ->get();
    if ($processlist1) {
        return response()->json([
            "code" => 200,
            "data" => $processlist1
        ]);
    }
    // -how to include filter based on parent_id' to the following query? 
    // \DB::table("process_def")
    //  ->select('id','name','process_cat','tenant_id')
    //  ->join('process_subtenant_rel', 'process_def.id', '=', 'process_subtenant_rel.process_id')
    //  ->where('process_subtenant_rel._at', '>=', date(Carbon::today()))
    //  ->get();


    }

        public function loadCategory()
    {
        $kpiCat = \DB::select(\DB::raw("select id, name from kpi_cat"));
        if ($kpiCat) {
            return response()->json([
                "code" => 200,
                "kpiCat" => $kpiCat
            ]);
        }
    }

    public function store(Request $request)
    {

            $prctype = \DB::table("process_cat")->insertGetId(//insert(
                [
                    // 'id'=>'',
                    'name' => $request->category_name,
                    // 'process_cat' => $request->Category,
                    'tenant_id' => env('TENANT_ID'),//1,
                    'updated_at' =>NULL,
                    // 'created_at' => NULL,


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
        $process_info =  \DB::table("process_cat")
        ->select('id','name','tenant_id')
        ->where('id',$id)
        ->first();

        $process = [];
        if ($process_info) {
            /*echo $process_info->date_from;
            die;*/
            $process['id'] = $process_info->id;
            $process['category_name'] = $process_info->name;
            $process['tenant'] = $process_info->tenant_id;
            


            return response()->json([
                "code" => 200,
                "data" => $process,
            ]);
        } else {
            return response()->json([
                "code" => 404,
                "msg" => "غير موجود"
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
        $now = date('Y-m-d H:i:s');

        $prc_update = \DB::table("process_cat")
        ->where('id', $id)
        ->update([
            'name' => $request->category_name,
            'updated_at'=>$now
            // 'process_cat' => $request->Category,

        
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
        // $prctype1 = \DB::table("process_subtenant_rel")->Where('process_id', $id)->delete();
        $prctype = \DB::table("process_cat")->Where('id', $id)->delete();

        if (!$prctype) {
            return response()->json([
                "code" => 404,
                // "msg" => "لا يمكن حذف طلب الإجازة"
            ]);
        }

            // $prctype->delete();

            return response()->json([
                "code" => 200,
                // "msg" => "تم حذف طلب الإجازة"
            ]);


    }

}

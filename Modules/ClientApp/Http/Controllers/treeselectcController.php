<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Modules\ClientApp\Entities\SubTenant;

class treeselectcController extends Controller
{
    //
    public function index()
    {



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





    public function subtenanttree($id)
    {

        $tenants1 = SubTenant::with('tree')->Where('parent_id', '<>',0)->Where('parent_id', '<>',1)->Where('parent_id', '<>',2)->Where('parent_id', '<>',3)->Where('parent_id','<>', null)->Where('parent_id', $id)->get();


        $tenants = SubTenant::with('children')->orWhere('parent_id', $id)

            ->get();
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
            return response()->json([
                "code" => 200,
                "subTenants" => $tenants1,
                "subTenantsdept" => $tenants
            ]);
        }

        return response()->json(["code" => 400]);
    }






}

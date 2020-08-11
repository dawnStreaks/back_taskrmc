<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\SubTenant;
use Modules\ClientApp\Http\Requests\SubTenantStore;
use Modules\ClientApp\Http\Requests\SubTenantUpdate;

class TenantController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:subtenant-list');
        $this->middleware('permission:subtenant-view', ['only' => ['show']]);
        $this->middleware('permission:subtenant-create', ['only' => ['store']]);
        $this->middleware('permission:subtenant-edit', ['only' => ['update']]);
        $this->middleware('permission:subtenant-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        $tenants = SubTenant::with('tree')->orWhere('parent_id', 0)->orWhere('parent_id', null)->where('tenant_id', \Auth::user()->tenant_id)->get();


        if ($tenants) {
            return response()->json([
                "code" => 200,
                "subTenants" => $tenants
            ]);
        }


        return response()->json(["code" => 400]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function tenantdata()
    {

        $subtenants = \DB::table('v_subtenant')->get();

        $colorcodes[] = '';
        $colorcodesarray[] = '';
        $isassistantparent='';
        foreach ($subtenants as $key => $subtenant) {
            //echo $i;
            //$parentID = $subtenant->parent_unit_id;
            $parentID = $subtenant->parent_unit_id;
            $unitID = $subtenant->unit_id;

            if ($subtenant->color_code != NULL) {
                $colorcodes[$unitID] = $subtenant->color_code;
                $colorcodeset = $subtenant->color_code;
            }

            if ($subtenant->color_code == NULL) {

                $colorcodes[$unitID] = $colorcodes[$parentID];
                $colorcodeset = $colorcodes[$unitID];

            }
//
            if ($subtenant->is_assistant == 0) {
                $isassistant = null;
                $isassistantparent=null;
//
            } else {
                $isassistant = $subtenant->is_assistant;
                $isassistantparent=$parentID;
            }


            $subtenanantvalues[] = array(
                'LabelName' => (string)$subtenant->unit_name,
                'Name' => (string)$subtenant->unit_id,
                'subtanentid' => $subtenant->type_id,
                'isassistant' => $isassistant,
                'colorcode' => '#' . (string)$colorcodeset,
                'ReportingPerson' => $parentID,
                'isassistantparent'=>$isassistantparent);

        }

        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "subTenants" => $subtenanantvalues,
                'colorcodes' => $colorcodes
            ]);
        }

    }

    public function tenantdataorg(Request $request)
    {
        $checkedValues = $request->orgdata;
        $subtenantsfull = \DB::table('v_subtenant')->get();

        foreach ($subtenantsfull as $key => $subtenantfull) {

            $parentIDfull = $subtenantfull->parent_unit_id;
            $unitIDfull = $subtenantfull->unit_id;

            if ($subtenantfull->color_code != NULL) {
                $colorcodesfull[$unitIDfull] = $subtenantfull->color_code;
//                $colorcodesetfull = $subtenantfull->color_code;
            }

            if ($subtenantfull->color_code == NULL) {

                $colorcodesfull[$unitIDfull] = $colorcodesfull[$parentIDfull];


            }
        }

        $subtenants = \DB::table('v_subtenant')
            ->whereIn('unit_id', explode(',', $checkedValues))
            ->orwhere('unit_id','<=',2)
            ->get();

        $i = 1;
        $isassistant = '';
        $colorcodes[] = '';
        $colorcodesarray[] = '';

        foreach ($subtenants as $key => $subtenant) {

            $parentID = $subtenant->parent_unit_id;
            $unitID = $subtenant->unit_id;

            if ($subtenant->color_code != NULL) {
                $colorcodes[$unitID] = $subtenant->color_code;
                $colorcodeset = $subtenant->color_code;
            }

            if ($subtenant->color_code == NULL) {

                $colorcodes[$unitID] = $colorcodesfull[$parentID];

                $colorcodeset = $colorcodes[$unitID];

            }
//
            if ($subtenant->is_assistant == 0) {
                $isassistant = null;
//
            } else {
                $isassistant = $subtenant->is_assistant;
            }


            $subtenanantvalues[] = array(
                'LabelName' => (string)$subtenant->unit_name,
                'Name' => (string)$subtenant->unit_id,
                'subtanentid' => $subtenant->type_id,
                'isassistant' => $isassistant,
                'colorcode' => '#' . (string)$colorcodeset,
                'ReportingPerson' => $parentID);


        }

        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "subTenants" => $subtenanantvalues,
                'colorcodes' => $colorcodes
            ]);
        }

    }


    public function create()
    {
        return view('clientapp::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(SubTenantStore $request)
    {
        $TenantId = \Auth::user()->tenant_id;

        $subtenant = new SubTenant;
        $subtenant->tenant_id = \Auth::user()->tenant_id;
        $subtenant->parent_id = $request->parent_id;
        $subtenant->name = $request->name;
        $subtenant->description = $request->description;
        //$subtenant->idGroupSubTenants = $request->idGroupSubTenants ;
        //$subtenant->idSubTenantType = $request->idSubTenantType;
        //$subtenant->is_bpmn = $request->is_bpmn;

        if ($subtenant->save()) {
            return response()->json([
                "code" => 200,
                "msg" => "data inserted successfully"
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => "error saving the data"
        ]);
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $subtenant = SubTenant::Where('tenant_id', auth()->user()->tenant_id)->find($id);

        if ($subtenant) {
            return response()->json([
                "code" => 200,
                "data" => $subtenant
            ]);
        }

        return response()->json([
            "code" => 404,
            "msg" => "data not found"
        ]);


    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('clientapp::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(SubTenantUpdate $request, $id)
    {
        $TenantId = \Auth::user()->tenant_id;

        $subtenant = SubTenant::Where('tenant_id', auth()->user()->tenant_id)->find($id);
        if (!$subtenant) {

            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);

        }
        $subtenant->parent_id = $request->parent_id;
        $subtenant->name = $request->name;
        $subtenant->description = $request->description;
        //$subtenant->idGroupSubTenants = $request->idGroupSubTenants ;
        //$subtenant->idSubTenantType = $request->idSubTenantType;
        //$subtenant->is_bpmn = $request->is_bpmn;

        if ($subtenant->save()) {
            return response()->json([
                "code" => 200,
                "msg" => "data updated successfully"
            ]);
        }

        return response()->json(["code" => 400]);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $subtenant = SubTenant::Where('tenant_id', auth()->user()->tenant_id)->find($id);

        if (!$subtenant) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }

        if ($subtenant->delete()) {
            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }

        return response()->json(["code" => 400]);

    }
}

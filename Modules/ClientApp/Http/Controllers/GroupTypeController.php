<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\TenantUserType;

class GroupTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $groupTypes = TenantUserType::where('tenant_id',$this->user->tenant_id)->get();

        if($groupTypes){
            return response()->json([
                "code" => 200 ,
                "data" => $groupTypes
            ]);
        }

        return response()->json([
            "code" => 404 ,
            "msg" => "not found"
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('clientapp::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $groupType = TenantUserType::where('tenant_id',$this->user->tenant_id)->find($id);

        if($groupType){
            return response()->json([
                "code" => 200 ,
                "data" => $groupType
            ]);
        }

        return response()->json([
            "code" => 404 ,
            "msg" => "not found"
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
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
}

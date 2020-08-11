<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Modules\ClientApp\Entities\Groups;
use Illuminate\Support\Facades\DB;
use Modules\ClientApp\Http\Requests\GroupStore ;
use Modules\ClientApp\Http\Requests\GroupUpdate ;


class GroupController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:group-list');
        $this->middleware('permission:group-create', ['only' => ['show']]);
        $this->middleware('permission:group-create', ['only' => ['store']]);
        $this->middleware('permission:group-edit', ['only' => ['update']]);
        $this->middleware('permission:group-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $user_groups = Groups::where('tenant_id' , auth()->user()->tenant_id)->get();

        if($user_groups){
            return response()->json([
                "code" =>200,
                "groups" => $user_groups
            ]);
        }

        return response()->json(["code"=>400]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GroupStore $request)
    {
        $newGroup = new Groups ;
        $newGroup->name = $request->name ;
        $newGroup->description = $request->description  ;
        $newGroup->tenant_id = $this->user->tenant_id ;
        //$newGroup->subtenant_id = $request->subtenant_id ;
        //$newGroup->tenant_user_type_id = $request->tenant_user_type_id ;
        $newGroup->bpm_ref = strtolower($request->name) ;

        if($newGroup->save()){

            $camundaGroupData = [
                "id" => (string)strtolower($newGroup->name) ,
                "name" => $newGroup->name ,
                "type" => $newGroup->tenant_user_type_id
            ] ;

            if($this->validateFunction($this->camunda->createSingleGroup($camundaGroupData))){
                /** Add group to the main tenant */
                $this->camunda->addTenantGroupMember($newGroup->tenant_id , $newGroup->id);

                return response()->json([
                    "code" => 200 ,
                    "msg" => "data inserted successfully"
                ]);
            }else{
                $newGroup->delete();
            }
            return response()->json([
                "code" => 400 ,
                "data" => "error saving the data"
            ]);
        }

        return response()->json(["code"=>400]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $group = Groups::Where('tenant_id',auth()->user()->tenant_id)->find($id) ;

        if($group){
            return response()->json([
                "code" => 200 ,
                "data" => $group
            ]);
        }

        return response()->json([
            "code" => 404 ,
            "msg" => "data not found"
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(GroupUpdate $request, $id)
    {


        DB::beginTransaction();
            $updateGroup = Groups::Where('tenant_id',$this->user->tenant_id)->find($request->id);
            if($updateGroup->update($request->only('name' , 'description' , 'tenant_user_type_id' , 'subtenant_id'))){
                $camundaGroupData = [
                    "id" => (string)strtolower($updateGroup->bpm_ref) ,
                    "name" => $updateGroup->name ,
                    "type" => $updateGroup->tenant_user_type_id ,
                ];

                if($this->validateFunction($this->camunda->updateSingleGroup($updateGroup->bpm_ref,$camundaGroupData))){
                    DB::commit();
                    return response()->json([
                        "code" => 200 ,
                        "msg" => "data updated successfully"
                    ]);
                }else{
                    DB::rollBack();
                }

                return response()->json([
                    "code" =>200 ,
                    "data" => $updateGroup
                ]);
            }

        return response()->json(["code"=>400]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id , Request $request)
    {
        $group = Groups::Where('tenant_id',$this->user->tenant_id)->find($id) ;

        if(!$group){
            return response()->json([
                "code" => 404 ,
                "msg" => "data not found"
            ]);
        }
        if($group->delete()){
            if($this->camunda->deleteSingleGroup($group->bpm_ref)){
                return response()->json([
                    "code" => 200 ,
                    "msg"  =>"deleted the record"
                ]);
            }
        }
        return response()->json([
            "code" => 400 ,
            "msg" => "error saving the data"
        ]);
    }

}

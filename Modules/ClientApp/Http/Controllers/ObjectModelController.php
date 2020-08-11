<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\ObjectModel;
use Modules\ClientApp\Http\Requests\ObjectModelStore;
use Modules\ClientApp\Http\Requests\ObjectModelUpdate;
use Spatie\Permission\Models\Permission;

class ObjectModelController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:models-list');
        $this->middleware('permission:models-view', ['only' => ['show']]);
        $this->middleware('permission:models-create', ['only' => ['store']]);
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $objectModel = ObjectModel::all();

        if ($objectModel) {
            return response()->json([
                "code" => 200,
                "objectModel" => $objectModel
            ]);
        }

        return response()->json(["code" => 400]);
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
    public function store(ObjectModelStore $request)
    {
//        $TenantId = \Auth::user()->idTenants;
//
        $prctype = new ObjectModel();
        $prctype->name = $request->name;

        $permisson = ['list', 'create', 'edit', 'delete', 'view'];

        foreach ($permisson as $value) {
            $user = Permission::where('name', '=', $request->name.'-'.$value)->first();
            if ($user === null) {
                Permission::create(['name' => $request->name.'-'.$value, 'guard_name' => 'api']);
            }
        }

        $prctype = ObjectModel::create(
            [
                'name' => $request->name
            ]
        );

        if ($prctype->save()) {
            return response()->json([
                "code" => 200,
                "msg" => "data inserted successfully"
            ]);
        }

        return response()->json(["code" => 400]);
    }


    public function edit()
    {
        return view('clientapp::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(ObjectModelUpdate $request, $id)
    {
        $prctype = ObjectModel::Where('id', $id);
        if (!$prctype) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }

        if ($prctype->update($request->all())) {
            return response()->json([
                "code" => 200,
                "msg" => "data updated successfully"
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => "error updating the data"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $prctype = ObjectModel::Where('idPRCType', $id);

        if (!$prctype) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }

        if ($prctype->delete()) {
            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }

        return response()->json(["code" => 400]);

    }

    public function show($id)
    {
        $prctype = ObjectModel::Where('id', $id)->first();

        if ($prctype) {
            return response()->json([
                "code" => 200,
                "objectModel" => $prctype
            ]);
        }

        return response()->json([
            "code" => 404,
            "msg" => "data not found"
        ]);
    }
}

<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\PRCType;
use Modules\ClientApp\Http\Requests\PRCTypeStore;
use Modules\ClientApp\Http\Requests\PRCTypeUpdate;

class PRCTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $PRCTypes = PRCType::all();

        if ($PRCTypes) {
            return response()->json([
                "code" => 200,
                "PRCTypes" => $PRCTypes
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
    public function store(PRCTypeStore $request)
    {
//        $TenantId = \Auth::user()->idTenants;
//
        $prctype = new PRCType;
        $prctype->TypeCode = $request->TypeCode;
        $prctype->Type = $request->Type;

        $prctype = PRCType::create(
            [
                'TypeCode' => $request->TypeCode,
                'Type' => $request->Type
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
    public function update(PRCTypeUpdate $request, $id)
    {

        $model = new PRCType($request->all());

        $model->setTable('PRCType');
        $query = $model->find($id);
        $updates["TypeCode"] = $request->TypeCode;
        $updates["Type"] = $request->Type;;

        if (!$query) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }

        if ($query->update($updates))  {
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

        $model = new PRCType();
        $model->setTable('PRCType');
        $query = $model->find($id);
        if (!$query) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }
       if ($query->delete()) {

            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }

        return response()->json(["code" => 400]);

    }

    public function show($id)
    {
        $prctype = PRCType::Where('idPRCType', $id)->first();

        if ($prctype) {
            return response()->json([
                "code" => 200,
                "data" => $prctype
            ]);
        }

        return response()->json([
            "code" => 404,
            "msg" => "data not found"
        ]);
    }
}

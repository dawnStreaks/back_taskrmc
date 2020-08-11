<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\PRCType;
use Modules\ClientApp\Entities\PriorityType;
use Modules\ClientApp\Http\Requests\PriorityTypeStore;
use Modules\ClientApp\Http\Requests\PriorityTypeUpdate;

class PriorityTypeController extends Controller
{

    public function index()
    {
        //$PriorityType = PriorityType::all();
        $PriorityType = \DB::table('TaskPriorityType')
            ->join('PRCType', 'TaskPriorityType.PRCType', '=', 'PRCType.IdPRCType')
            ->select('TaskPriorityType.*', 'PRCType.Type')
            ->where('TaskPriorityType.deleted_at', null)
            ->get();

        if ($PriorityType) {
            return response()->json([
                "code" => 200,
                "PriorityType" => $PriorityType
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
    public function store(PriorityTypeStore $request)
    {
//        $TenantId = \Auth::user()->idTenants;
//
        $PriorityType = new PriorityType;
        $PriorityType->TypeCodeMin = $request->TypeCodeMin;
        $PriorityType->TypeCodeMax = $request->TypeCodeMax;
        $PriorityType->PRCType = $request->PRCType;

        if ($PriorityType->save()) {
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
    public function update(PriorityTypeUpdate $request)
    {
        $PriorityType = PriorityType::find($request->id);

        $PriorityType->TypeCodeMin = $request->TypeCodeMin;
        $PriorityType->TypeCodeMax = $request->TypeCodeMax;
        $PriorityType->PRCType = $request->PRCType;

        if ($PriorityType->save()) {
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
        $PriorityType = PriorityType::Where('idTaskPriorityType', $id);

        if (!$PriorityType) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }

        if ($PriorityType->delete()) {
            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }

        return response()->json(["code" => $PriorityType]);

    }

    public function show($id)
    {
        $PriorityType = \DB::table('TaskPriorityType')
            ->join('PRCType', 'TaskPriorityType.PRCType', '=', 'PRCType.IdPRCType')
            ->select('TaskPriorityType.*', 'PRCType.Type', 'PRCType.TypeCode')
            ->where('idTaskPriorityType', $id)
            ->first();

        //$prctype = PriorityType::Where('idTaskPriorityType',$id)->first();

        if ($PriorityType) {
            return response()->json([
                "code" => 200,
                "data" => $PriorityType
            ]);
        }

        return response()->json([
            "code" => 404,
            "msg" => "data not found"
        ]);
    }
}

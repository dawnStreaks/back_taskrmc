<?php

namespace Modules\ClientApp\Http\Controllers;

use Modules\ClientApp\Reports\UnitPerformanceReport;
use Illuminate\Http\Request;

class UnitPerformanceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
        $this->middleware("guest");
    }

     
    public function index(Request $request)
    {
        $language=$request->lang;
        // $trans=json_decode($request->translation);

       $report = new UnitPerformanceReport(array("language"=>$language));//,"translation"->$trans));
        $report->run();
        return view("unitperformancereport",["report"=>$report]);
    }

   
}

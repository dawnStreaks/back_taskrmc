<?php

namespace Modules\ClientApp\Http\Controllers;

use Modules\ClientApp\Reports\KpiPerformanceReport;
use Illuminate\Http\Request;

class KpiPerformanceReportController extends Controller
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
        $sect=$request->sect;
        $org=$request->org;
        // $trans=json_decode($request->translation);
        $back=$request->back;

       $report = new KpiPerformanceReport(array("language"=>$language,
       "sect"=>$sect,
       "org"=>$org,
       "back"=>$back));
        $report->run();
        return view("kpiperformancereport",["report"=>$report]);
    }

   
}

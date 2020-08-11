<?php

namespace Modules\ClientApp\Http\Controllers;

use Modules\ClientApp\Reports\KpiPivotReport;
use Illuminate\Http\Request;

class KpiPivotReportController extends Controller
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
        $sect=$request->sect;
        $org=$request->org;
        $back=$request->back;

       $report = new KpiPivotReport(array("language"=>$language,
       "sect"=>$sect,
       "org"=>$org,
       "back"=>$back));
        $report->run();
        return view("kpipivotreport",["report"=>$report]);
    }

   
}

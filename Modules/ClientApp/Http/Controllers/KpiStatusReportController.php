<?php

namespace Modules\ClientApp\Http\Controllers;

use Modules\ClientApp\Reports\KpiStatusReport;
use Illuminate\Http\Request;

class KpiStatusReportController extends Controller
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
        $back=$request->back;


        // $trans=json_decode($request->translation);
       $report = new KpiStatusReport(array(
           "language"=>$language,
           "sect"=>$sect,
           "org"=>$org,
           "back"=>$back));//,"translation"->$trans));
        $report->run();
        return view("kpistatusreport",["report"=>$report]);
    }

   
}

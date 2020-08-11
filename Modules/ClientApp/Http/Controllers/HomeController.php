<?php

namespace Modules\ClientApp\Http\Controllers;

use Modules\ClientApp\Reports\MyReport;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
        $this->middleware("guest");
    }

     
    public function index()//(Request $request)
    {
        // $language=$request->lang;
//var_dump($language);
        $report = new MyReport;
        // (array(
           // "language"    =>$language,
        // ));
        $report->run();
        return view("report",["report"=>$report]);
    }

}

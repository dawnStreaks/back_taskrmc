<?php

namespace Modules\ClientApp\Http\Controllers;

use Modules\ClientApp\Reports\KpiValuesReport;
use Illuminate\Http\Request;

class KpiValuesReportController extends Controller
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

        $report = new KpiValuesReport(array("language"=>$language));
        $report->run();
        return view("kpivaluesreport",["report"=>$report]);
    }
    public function statuslink(Request $request)
    {
        $output =$request->cluster;
    //  $Mixed = json_decode($Text);
    // Recommended
parse_str($output,$Text);
    // $Text= explode("**" ,$Text);
    // print_r($output['array']['kpi_name']);
    //    $language=$output[];
    //    var_dump($language);

        $kpi=$Text['array']['kpi'];
        $mtp=$Text['array']['mtp'];
        $org_unit=$Text['array']['org_unit'];
        $kpi_symbol=$Text['array']['kpi_symbol'];
        $kpi_name=$Text['array']['kpi_name'];
        $value_type=$Text['array']['value_type'];
        $language=$request->lang;


        $report = new KpiValuesReport(array("language"=>$language,"kpi"=>$kpi,"mtp"=>$mtp,"org_unit"=>$org_unit,"kpi_symbol"=>$kpi_symbol,"kpi_name"=>$kpi_name,"value_type"=>$value_type));
    //             $report = new KpiValuesReport(array("language"=>$language,"kpi"=>$kpi,"mtp"=>$mtp,"org_unit"=>$org_unit));

         $report->run();
        return view("kpivaluesreport",["report"=>$report]);
    }

//     public function get_text(Request $request)
//     {
//         $userId =  $request->textbit;
//         $userId=   $request->language;
//         $translation=
//         $report = new ArrayReport(array(
//             "supervision_id"=>$text_bit
//         ));
//         $input = $request->data1;
//         $filename=app_path()."/Reports/".$userId.".view.php";
// //        echo $filename;
// //        die();

//         file_put_contents($filename,$input);
//         chmod($filename, 0777);
//         return response()->json($input);


//     }

}

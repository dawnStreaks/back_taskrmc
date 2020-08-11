<?php

use \koolreport\widgets\koolphp\Table;
use \koolreport\processes\CalculatedColumn;
use \koolreport\inputs\BSelect;
use \koolreport\inputs\Select;
use \koolreport\processes\Sort;
use \koolreport\inputs\Select2;
use \koolreport\pivot\processes\PivotExtract;
// use \koolreport\datagrid\DataTables;
// use \koolreport\sparklines;
// use \koolreport\inputs\DateTimePicker;
use \koolreport\inputs\CheckBoxList;
use \koolreport\pivot\widgets\PivotTable;
use Modules\ClientApp\Reports\UnitPerformanceReport;
use \koolreport\processes\Filter;
use \koolreport\inputs\RadioList;
// var_dump($_POST['section']);
$language = '';
if (isset($this->params['language']) && !empty($this->params['language'])) {
    $language = $this->params['language'];
}

$sector_name = $this->dataStore('sector_name');

$sector11 = $sector_name->get(0, "name");
$transtable = $this->dataStore('translation');

$performing2=array();
$nonperforming2=array();
$total_perf=$this->dataStore('chartTable1')->get(0,"{{all}}");
$total_nonperf=$this->dataStore('chartTable2')->get(0,"{{all}}");
$total=$total_perf+$total_nonperf;
if(isset($_POST['sector'])&&(!empty($_POST['sector'])))
{
    if((isset($_POST['section']))&&(!empty($_POST['section'])))
    {
        $x='sub_name';

    }
    else{
        $x='dept_name';

    }

}
if(empty($_POST['sector']))
$x='sector_name';

if($this->dataStore("performing11")->countData()>0)
{
$performing22 = $this->dataStore('performing11')->only($x,'perf_sum_w')
->process(new CalculatedColumn(array(
    "perf_sum_w"=>array(
        "exp"=>function($data){
            // $total=$this->dataStore('chartTable1')->get(0,'count');
            $total_perf=$this->dataStore('chartTable1')->get(0,"{{all}}");
            $total_nonperf=$this->dataStore('chartTable2')->get(0,"{{all}}");
            $total=$total_perf+$total_nonperf;
            $value=($data['perf_sum_w']/$total)*100;
            return  round($value, 2);
            // return ($data['perf_sum_w']/$total)*100;
        }),
)));
$performing2 = $performing22->data();//toJson();//


}
if($this->dataStore("nonperforming11")->countData()>0)
{
$nonperforming22 = $this->dataStore('nonperforming11')->only($x,'nonperf_sum_w')
->process(new CalculatedColumn(array(
    "nonperf_sum_w"=>array(
        "exp"=>function($data){
            $total_perf=$this->dataStore('chartTable1')->get(0,"{{all}}");
            $total_nonperf=$this->dataStore('chartTable2')->get(0,"{{all}}");
            $total=$total_perf+$total_nonperf;
            $value=($data['nonperf_sum_w']/$total)*100;
            return  round($value, 2);
            
        }),
)));
$nonperforming2 = $nonperforming22->data();//t$performing2 = $performing22->data();//toJson();//

}

//----------------Array Merge Code-------------------
$output = array();
if(($this->dataStore("nonperforming11")->countData()>0)||($this->dataStore("performing11")->countData()>0))
{


$arrayAB = array_merge($performing2, $nonperforming2);
foreach ( $arrayAB as $value ) {
  $id = $value[$x];
  if ( !isset($output[$id]) ) {
    $output[$id] = array();
  }
  $output[$id] = array_merge($output[$id], $value);
}

//-----------------filling keys---------------
foreach($output as $key => $value){
   
    if(!in_array('perf_sum_w', array_keys($value))) {

           $output[$key]['perf_sum_w'] =0;            
       }
       if(!in_array('nonperf_sum_w', array_keys($value))) {

           $output[$key]['nonperf_sum_w'] =0;            
       }

   }
}
// echo "<pre>";
// print_r($output);

?>

<!DOCTYPE html>
<?php if ($language == 'ar')
    $dir = "rtl";
else
    $dir = "ltr";
?>

<html dir="<?php echo $dir; ?>">
<!-- <html dir="rtl" onchange="console.log('value of name field is changed')"> -->
<head>
    <meta charset="utf-8">
    <title>لوحة متابعة المؤشرات
    </title>
    <!-- <link rel='stylesheet' href='../../../assets/font-awesome/css/font-awesome.min.css'> -->
    <!-- <script src="../../../public/koolreport_assets/PivotTable.js"></script>  -->
    <style>
        /* .pivotTable{
            vertical-align: top;
        } */

        /* g .c3-legend-item-tile {
            /* fill: #fff; */
            /* stroke: #fff;
            stroke-width: 0;*/
            /* transform:translate(100.5,205.5); */
            /* padding-left: 10px; */

            /* opacity: ; 
        } */

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox  */
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* rect{
           fill-opacity:0.0;
           transform: translate(-50px, -50px);
              box-sizing: content-box;
            } */
        svg {
            /* padding-right:100px; */
            /* max-height: 300px; */
            /* padding-bottom: 0%;
            margin-bottom: 0%; */
            /* float:right; */
            /* transform: scale(0.75); */
            overflow: visible;
            /* // translate(-50px, -110px) ; */
        }

        /*.table-bordered >tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th{    border: 0px !important;
             /* /* solid     #ddd;
        } */
        #form2 {
            display: inline;
        }

        .fa1 i {
            display: none !important;
            /* text-align: <\?php echo $language=='ar'?'right':'left' ;?>;  */

            /* opacity:0; */
        }

        .pivot-table table table-bordered {
            /* transform: translate(0%, -50%);
            display: inline-block;
            vertical-align: top; */

            /* margin-bottom:200px; */
        }

        #pivot2 {
            padding-top: 100px;
            /* display: inline-block; */
            vertical-align: top;
        }

        .pivot-row-header-text {
            text-align: <?php echo $language=='ar'?'right':'left' ;?>;

        }

        .pivot-data-field-zone {
            display: visible;
        }

       
        .table-bordered {
            border: 0px !important;
        }

        /* .pivot-column{
            border:0px;
        } */
        .select2 {
            width: 100% !important;
            min-height: 40px;
        }

        @media print {
            .table {
                /* font-size: 50%; */
                transform: <?php echo $language=='ar'?'scale(0.7) translate(205px,50px); ':"" ;?>;
                /* margin-right:20px;
                padding-right:20px; */
            }

            table td {
                padding-left: 5px;
                padding-right: 5px;
            }

            .pivot-row-header-text i {
                visibility: hidden !important;
            }
        }
    </style>
<body>
<p id="printarea"></p>
<div id="printissue">
    <div class="row" style="background-color:#ffffff;margin-left:10px;margin-right:5px;margin-top:0px;
    margin-bottom:30px;
    padding-top:30px;padding-right:10px;padding-left:10px;">
        <h4 class="mb-0 pt-2" style="text-align:center;color: #20a8d8;font-size: 20px;font-weight: normal;">
            <!-- </?php echo $language == 'ar' ? 'تقرير أداء  الوحدات' : 'Unit Performance Report'; ?> -->
            <?php $textbit = 'title';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?> 
            
        </h4>
        <scan class="form-group"
              style="float:<?php echo $language == 'ar' ? 'left' : 'right'; ?>;padding-right:150px;padding-left:150px;">
            <script> document.write(new Date().toDateString()); </script>
        </scan>

        <?php if ($language == 'ar') {
            $dir = "rtl";
            $lin = "left";
        } else {
            $dir = "ltr";
            $lin = "right";
        }
        ?>
        <div style="background-color:#ffffff;border: 1px solid #a5aeb7;position:relative;" class="col-md-12"
             style="float:right;">
            <!-- <div class="col-md-10"></div> -->

            <div id="button1" dir="rtl" style="float:<?php echo $lin; ?>">
                <button onclick="myFunction()" style="float:left;border:none;background-color: #ffffff;"><i
                        style="padding-top:5px;color:#a9a9a9;" class="fa fa-angle-up 4x" title="toggle"></i></button>
                <form id="form2" method="post">
                    <input type="hidden" id="radiolist1" name="radiolist1" value="<?php echo($this->params["radiolist"]); ?>">
                    <input type="hidden" id="sector1" name="sector1" value="<?php echo($this->params["sector"]); ?>">
                    <input type="hidden" id="section1" name="section1" value="<?php echo($this->params["section"]); ?>">
                    <input type="hidden" id="mtp1" name="mtp1" value="<?php echo($this->params["mtp"]); ?>">
                    <input type="hidden" id="periodicity1" name="periodicity1" value="<?php echo($this->params["periodicity"]); ?>">
                    <input type="hidden" id="year_no1" name="year_no1" value="<?php echo($this->params["year_no"]); ?>">
                    <input type="hidden" id="top_performing1" name="top_performing1" value="<?php echo($this->params["top_performing"]); ?>">
                    <input type="hidden" id="performing1" name="performing1" value="<?php echo($this->params["performing"]); ?>">
                    <input type="hidden" id="nonperforming1" name="nonperforming1" value="<?php echo($this->params["nonperforming"]); ?>">

                    <button class="form-group" onClick="javascript:printDiv()"
                            style="float:left;border:none;background-color: #ffffff;"><i
                            style="padding-top:5px;color:#a9a9a9;font-size:12px" class="fa fa-print" title="print"></i>
                    </button>
                    <?php //echo "<pre>"; var_dump($this->params);?>
                    <button class="form-group" onClick="javascript:expandPivot()" name="expand" value="<?php echo
                    ($this->params['expand'] == 0) ? '2' : '0' ?>"
                            style="float:left;border:none;background-color: #ffffff;"><i
                            style="padding-top:5px;color:#a9a9a9;font-size:12px" class="<?php echo
                        ($this->params['expand'] == 0) ? 'fa fa-plus-square' : 'fa fa-minus-square' ?>"
                            title="expand"></i></button>

                </form>
            </div>
        </div>
        <br/>
        <?php $new = $this->dataStore('user_details');
        ?>
        <?php $style = "";
        if (empty($_POST['sector']) && (empty($_POST['section'])) && (empty($_POST['mtp'])) && (empty($_POST['periodicity'])) && (empty($_POST['year_no'])) && (empty($_POST['top_performing'])) && (empty($_POST['radiolist']))) {
            // if(empty($_POST['filter'])){
            $style = 'display:none !important;';
        } else {
            $style = 'display:block !important;';
        } ?>
        <div id="myDIV" dir="<?php echo $language == 'ar' ? "rtl" : "ltr"; ?>"
             style="overflow:auto;width:100%;background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:30px;padding-right:30px;<?php echo $style; ?>">
            <form id="form1" method="post">
                <!-- @method('PUT') -->
                <?php $expVal = 0;
                if ($this->params['expand'] == 0) {
                    $expVal = $this->params['expand1'];
                } elseif ($this->params['expand1'] == 0) {
                    $expVal = $this->params['expand'];
                }
                ?>
                <input type="hidden" id="expand1" name="expand1" value="<?php echo $expVal; ?>">
                <!--<input type="hidden" id="expand" name="expand" value="<?php /*echo($this->params["expand"]); */ ?>">-->
                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'sector';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                    <?php
                    select2::create(array(
                        // "multiple"=>false,
                        "name" => "sector",
                        "defaultOption" => array("--" => ""),
                        "dataStore" => $this->dataStore("sector1"),
                        "dataBind" => array(
                            "text" => "name",
                            "value" => "id",
                        ),
                        // "attributes" => array(
                        //     "class" => "col-md-4 form-control"                            // $('#section').val(null).trigger(\"change\");

                        // ),
                        "clientEvents" => array(
                            "change" => "function(params){
                            $('#form1').submit();


                        }"
                        ),
                    ));
                    ?>
                </div>
                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'org_unit';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                    <?php
                    Select2::create(array(
                        // "multiple"=>true,
                        "name" => "section",
                        "defaultOption" => array("--" => ""),
                        "dataStore" => $this->dataStore("section1"),
                        "dataBind" => array(
                            "text" => "name",
                            "value" => "id",
                        ),
                        // "attributes" => array(
                        //     "class" => "col-md-4 form-control"
                        // ),
                        "clientEvents" => array(
                            "change" => "function(params){
                            $('#form1').submit();


                        }"
                        ),
                    ));
                    ?>
                </div>
                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong></strong>
                    <div dir="ltr">
                        <?php
                        RadioList::create(array(
                            "name" => "radiolist",
                            "data" => array(
                                $language == 'en' ? $transtable->where('key_name', 'performing')->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', 'performing')->where('key_pos','unit_performance_report')->get(0, "value_ar") => 1,
                                $language == 'en' ? $transtable->where('key_name', 'nonperforming')->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', 'nonperforming')->where('key_pos','unit_performance_report')->get(0, "value_ar") => 2,
                                $language == 'en' ? $transtable->where('key_name', 'top_perf_nonperf')->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', 'top_perf_nonperf')->where('key_pos','unit_performance_report')->get(0, "value_ar") => 3,

                            ),
                            "clientEvents" => array(
                                "change" => "function(params){
                            $('#form1').submit();
                        }"
                            ),
                        ));
                        ?>
                    </div>
                </div>
                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'top_performing_greater_than';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>

                    <input class="col-md-4 form-control" type="number" id="performing" name="performing" min="80"
                           max="100" value="<?php echo $this->params["performing"]; ?>">

                </div>

                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'top_nonperforming_less_than';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>

                    <input class="col-md-4 form-control" type="number" id="nonperforming" name="nonperforming" min="0"
                           max="50" value="<?php echo $this->params["nonperforming"]; ?>">

                </div>

                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'no_of_records';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                    <?php
                    select2::create(array(
                        // "multiple"=>true,
                        "name" => "top_performing",

                        "data" => array(
                            "5" => 5,
                            "10" => 10,
                            "15" => 15,

                        ),

                        "clientEvents" => array(
                            "change" => "function(params){
                            $('#form1').submit();
                        }"
                        ),
                    ));
                    ?>
                </div>
                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'year_no';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                    <?php
                    select2::create(array(
                        // "multiple"=>true,
                        "name" => "year_no",
                        // "defaultOption" => array("--" => ""),
                        // "attributes" => array(
                        //     "class" => "col-md-4 form-control"
                        // ),
                        "data" => array(
                            // "1"=>1,
                            $language == 'en' ? $transtable->where('key_name', 'year1')->get(0, "value_en") : $transtable->where('key_name', 'year1')->get(0, "value_ar") => 1, //"quarter"=>3,
                            $language == 'en' ? $transtable->where('key_name', 'year2')->get(0, "value_en") : $transtable->where('key_name', 'year2')->get(0, "value_ar") => 2, //"quarter"=>3,
                            $language == 'en' ? $transtable->where('key_name', 'year3')->get(0, "value_en") : $transtable->where('key_name', 'year3')->get(0, "value_ar") => 3, //"quarter"=>3,
                        ),

                        "clientEvents" => array(
                            "change" => "function(params){
                            $('#form1').submit();
                        }"
                        ),
                    ));
                    ?>
                </div>
              
                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'period';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                    <?php
                    select2::create(array(
                        // "multiple"=>true,
                        "name" => "periodicity",

                        "data" => array(
                            $language == 'en' ? $transtable->where('key_name', '1st_quarter')->get(0, "value_en") : $transtable->where('key_name', '1st_quarter')->get(0, "value_ar") => 'Q1', //"quarter"=>3,
                            $language == 'en' ? $transtable->where('key_name', '2nd_quarter')->get(0, "value_en") : $transtable->where('key_name', '2nd_quarter')->get(0, "value_ar") =>'Q2',//"semi_annual"=>6,
                            $language == 'en' ? $transtable->where('key_name', '3rd_quarter')->get(0, "value_en") : $transtable->where('key_name', '3rd_quarter')->get(0, "value_ar")=> 'Q3',//"annual"=>1=> 36,//"every_3_years"=>36,
                            $language == 'en' ? $transtable->where('key_name', '4th_quarter')->get(0, "value_en") : $transtable->where('key_name', '4th_quarter')->get(0, "value_ar")=>'Q4',
                            $language == 'en' ? $transtable->where('key_name', '1st_biannual')->get(0, "value_en") : $transtable->where('key_name', '1st_biannual')->get(0, "value_ar")=>'H1',
                            $language == 'en' ? $transtable->where('key_name', '2nd_biannual')->get(0, "value_en") : $transtable->where('key_name', '2nd_biannual')->get(0, "value_ar")=>'H2',
                            $language == 'en' ? $transtable->where('key_name', 'annual')->get(0, "value_en") : $transtable->where('key_name', 'annual')->get(0, "value_ar")=>'Y',


                        ),

                        "clientEvents" => array(
                            "change" => "function(params){
                            $('#form1').submit();


                        }"
                        ),
                    ));
                    ?>
                </div>
                <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
                    <strong>
                        <?php $textbit = 'mtp';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_en") : $transtable->where('key_name', $textbit)->where('key_pos','unit_performance_report')->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                    <?php
                    select2::create(array(
                        // "multiple"=>true,
                        "name" => "mtp",
                        "placeholder" => "اختر ",
                        "dataStore" => $this->dataStore("mtp1"),
                        "dataBind" => array(
                            "text" => "name",
                            "value" => "id",
                        ),
                        "clientEvents" => array(
                            "change" => "function(params){
                            console.log(params);
                            $('#form1').submit();


                        }"
                        ),
                    ));
                    ?>
                </div>
            </form>
        </div>
        <div class="col-md-12" id="performance" style="overflow:auto;width:100%;background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:0px;padding-right:0px;">
            <div class="col-md-8">
                <div id="pivot1"
                     style="overflow:auto;width:100%;background-color:#ffffff; padding-top:20px;padding-bottom:20px;padding-left:0px;padding-right:0px;margin-left:-10px;margin-right:-10px;">
                    <div>
                        <!--<table width="100%">
                            <col style="width:60%;">
                            <col style="width:40%;">
                            <tr>
                                <td id="pivot2">-->

                        <?php
                        // $a=$this->dataStore('performing1')->only('sector_name')->data();
                        // $result = array_unique($a);
                        // // // $this->dataStore('result')=$this->dataStore('result')(perf_name,sub_name,sector_name,unit_name,perf_sum_w)
                        // Table::create(array(
                        //     "dataSource"=>$this->dataStore('nonperforming1pie')
                        // ));
                        $datastore = "";
                        if ($this->params["radiolist"] === "1") {

                            PivotTable::create(array(
                                //    'dataStore'=>($this->params["radiolist"]=="1"?$this->datastore('performing'):($this->params["radiolist"]=="2"?$this->datastore('nonperforming'):($this->params["radiolist"]=="3"?$this->datastore('user_details'):""))),
                                'dataStore' => $this->datastore('performing'),//$datastore,
                                'rowDimension' => 'row',//$this->datastore('performing'),//

                                'measures' => array(
                                    // 'dollar_sales - sum',
                                    'perf_name',
                                    'sector_name',
                                    'dept_name',
                                    'sub_name',

                                    // 'unit_name',
                                    'perf_sum_w',
                                    // 'perf_sum_w1',
                                ),
                                'rowSort' => array(
                                    'perf_sum_w1' => 'asc',
                                ),
                                //  'rowSort' => array(
                                //      'kpi_perf' => 'desc',
                                //   ),

                                // 'rowSort' => array(
                                //     'kpi_perf' => function($a, $b) {
                                //         // var_dump(hello);
                                //         return (int)$a < (int)$b;
                                //     },
                                // 'orderDay' => 'asc'
                                // ),
                                'map' => array(
                                    'rowHeader' => function ($rowHeader, $headerInfo) {
                                        $v = $rowHeader;
                                        if (isset($headerInfo['childOrder']))
                                            $v = $headerInfo["fieldName"] == "id" ? substr($headerInfo['childOrder'], -1, 1) : $v;
                                        return $v;
                                    },
                                    'dataCell' => function ($value, $cellInfo) {
                                        if ($cellInfo["fieldName"] === "perf_sum_w") {
                                            $cellInfo["formattedValue"] = $value . "%";
                                            // }else if ($cellInfo["fieldName"] === "hours"){
                                            // $cellInfo["formattedValue"] => round($value,0);
                                        }
                                        return $cellInfo["formattedValue"];
                                    }
                                ),
                                'hideTotalRow' => true,
                                'hideTotalColumn' => true,
                                'hideSubtotalRow' => true,
                                'hideSubtotalColumn' => true,
                                //    'showDataHeaders'=>true,
                                'rowCollapseLevels' => $this->params['expand'] == 2 || (isset($_POST['expand1']) &&
                                    $_POST['expand1'] == 2) ?
                                    array(7) : array(1),

                                'headerMap' => array(
                                    'perf_name' => ($language == 'en' ? $transtable->where('key_name', 'perf_name')->get(0, "value_en") : $transtable->where('key_name', 'perf_name')->get(0, "value_ar")),
                                    'dept_name' => ($language == 'en' ? $transtable->where('key_name', 'dept_name')->get(0, "value_en") : $transtable->where('key_name', 'dept_name')->get(0, "value_ar")),//'Org Unit',
                                     'sector_name' => ($language == 'en' ? $transtable->where('key_name', 'sector')->get(0, "value_en") : $transtable->where('key_name', 'sector')->get(0, "value_ar")),//'Next Reading Date',
                                    'sub_name' => ($language == 'en' ? $transtable->where('key_name', 'org_unit')->get(0, "value_en") : $transtable->where('key_name', 'org_unit')->get(0, "value_ar")),//'Org Unit',
                                    // 'id' => '#',//'Sequence',
                                    // 'unit_name' => ($language == 'en' ? $transtable->where('key_name', 'unit_name')->get(0, "value_en") : $transtable->where('key_name', 'unit_name')->get(0, "value_ar")),//'Symbol',
                                    'perf_sum_w'=>($language == 'en' ? $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_ar")),//'KPI Name',
                                    // 'perf_sum_w1'=>($language == 'en' ? $transtable->where('key_name', 'perf_sum_w1')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w1')->get(0, "value_ar")),//'KPI Name',

                                ),
                                'cssClass' => array(
                                    'rowHeader' => function ($value, $cellInfo) {

                                        if (($cellInfo['fieldName'] === "id") || ($cellInfo['fieldName'] === "sub_name") || ($cellInfo['fieldName'] === "perf_sum_w"))
                                            // <script>$(this).find('.fa-circle').removeClass('fa-circle')</script>

                                            return "fa1";
                                    }
                                ),
                            ));
                        } else if ($this->params["radiolist"] === "2") {
                            // var_dump("nonperforming");
                            //$datastore=$this->datastore('nonperforming');
                            PivotTable::create(array(
                                //    'dataStore'=>($this->params["radiolist"]=="1"?$this->datastore('performing'):($this->params["radiolist"]=="2"?$this->datastore('nonperforming'):($this->params["radiolist"]=="3"?$this->datastore('user_details'):""))),
                                'dataStore' => $this->datastore('nonperforming'),//$datastore,
                                'rowDimension' =>'row',// $this->datastore('performing'),//
                                //    'rowSort' => array(
                                //     'kpi_perf' => 'desc'
                                //     ),

                                'measures' => array(
                                    // 'dollar_sales - sum',
                                    'perf_name',
                                    'sector_name',
                                    'dept_name',
                                    'sub_name',
                                    // 'id',
                                    // 'unit_name',
                                    'perf_sum_w',
                                    //  'perf_sum_w1',
                                ),
                                // 'rowSort' => array(
                                //     'perf_sum_w1 - sum' => 'asc',
                                // ),
                                //  'rowSort' => array(
                                //      'kpi_perf' => 'desc',
                                //   ),

                                // 'rowSort' => array(
                                //     'kpi_perf' => function($a, $b) {
                                //         // var_dump(hello);
                                //         return (int)$a < (int)$b;
                                //     },
                                // 'orderDay' => 'asc'
                                // ),
                                'map' => array(
                                    'rowHeader' => function ($rowHeader, $headerInfo) {
                                        $v = $rowHeader;
                                        if (isset($headerInfo['childOrder']))
                                            $v = $headerInfo["fieldName"] == "id" ? substr($headerInfo['childOrder'], -1, 1) : $v;
                                        return $v;


                                    },
                                    'dataCell' => function ($value, $cellInfo) {
                                        if ($cellInfo["fieldName"] === "perf_sum_w") {
                                            $cellInfo["formattedValue"] = round($value, 0) . "%";
                                            // }else if ($cellInfo["fieldName"] === "hours"){
                                            // $cellInfo["formattedValue"] => round($value,0);
                                        }
                                        return $cellInfo["formattedValue"];
                                    }
                                ),
                                'hideTotalRow' => true,
                                'hideTotalColumn' => true,
                                'hideSubtotalRow' => true,
                                'hideSubtotalColumn' => true,
                                //    'showDataHeaders'=>true,
                                'rowCollapseLevels' => $this->params['expand'] == 2 || (isset($_POST['expand1']) &&
                                    $_POST['expand1'] == 2) ?
                                    array(7) : array(1),

                                'headerMap' => array(
                                    'perf_name' => ($language == 'en' ? $transtable->where('key_name', 'perf_name')->get(0, "value_en") : $transtable->where('key_name', 'perf_name')->get(0, "value_ar")),
                                    'dept_name' => ($language == 'en' ? $transtable->where('key_name', 'dept_name')->get(0, "value_en") : $transtable->where('key_name', 'dept_name')->get(0, "value_ar")),//'Org Unit',
                                    'sector_name' => ($language == 'en' ? $transtable->where('key_name', 'sector')->get(0, "value_en") : $transtable->where('key_name', 'sector')->get(0, "value_ar")),//'Next Reading Date',
                                    'sub_name' => ($language == 'en' ? $transtable->where('key_name', 'org_unit')->get(0, "value_en") : $transtable->where('key_name', 'org_unit')->get(0, "value_ar")),//'Org Unit',
                                    // 'id' => '#',//'Sequence',
                                    // 'unit_name' => ($language == 'en' ? $transtable->where('key_name', 'unit_name')->get(0, "value_en") : $transtable->where('key_name', 'unit_name')->get(0, "value_ar")),//'Symbol',
                                    'perf_sum_w'=>($language == 'en' ? $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_ar")),//($language == 'en' ? $transtable->where('key_name', 'perf_sum_w')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w')->get(0, "value_ar")),//'KPI Name',

                                    // 'perf_sum_w1 - sum'=>($language == 'en' ? $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_ar")),//'KPI Name',
                                ),
                                'cssClass' => array(
                                    'rowHeader' => function ($value, $cellInfo) {

                                        if (($cellInfo['fieldName'] === "id")  || ($cellInfo['fieldName'] === "sub_name") || ($cellInfo['fieldName'] === "perf_sum_w"))
                                            // <script>$(this).find('.fa-circle').removeClass('fa-circle')</script>

                                            return "fa1";
                                    }
                                ),

                            ));
                        } else if ($this->params["radiolist"] === "3") {


                            PivotTable::create(array(
                                //    'dataStore'=>($this->params["radiolist"]=="1"?$this->datastore('performing'):($this->params["radiolist"]=="2"?$this->datastore('nonperforming'):($this->params["radiolist"]=="3"?$this->datastore('user_details'):""))),
                                'dataStore' => $this->datastore('combined'),//$datastore,
                                'rowDimension' => 'row',//$this->datastore('performing'),//
                                //    'rowSort' => array(
                                //     //  'sector_name'=>'desc',  
                                //     // 'perf_name'=>'desc',

                                //     'perf_sum_w1' => 'asc',

                                //     ),

                                'measures' => array(
                                    // 'dollar_sales - sum',
                                    'perf_name',
                                    'sector_name',
                                    'dept_name',
                                    'sub_name',
                                    // 'id',
                                    'perf_sum_w',
                                    // 'perf_sum_w1',

                                    // 'unit_name',
                                    // 'perf_sum_w1 - sum',
                                    //    'kpi_perf-count',
                                ),
                                'rowSort' => array(
                                    'perf_name'=>'desc',
                                    // 'perf_sum_w1-sum'=>'asc',
 
                                    'perf_sum_w1' => 'asc',

                                // 'perf_sum_w1 - sum' => 'asc',


                                ),

                                //  'rowSort' => array(
                                //      'kpi_perf' => 'desc',
                                //   ),

                                // 'rowSort' => array(
                                //     'kpi_perf' => function($a, $b) {
                                //         // var_dump(hello);
                                //         return (int)$a < (int)$b;
                                //     },
                                // 'orderDay' => 'asc'
                                // ),
                                'map' => array(
                                    'rowHeader' => function ($rowHeader, $headerInfo) {
                                        $v = $rowHeader;
                                        if (isset($headerInfo['childOrder']))
                                            $v = $headerInfo["fieldName"] == "id" ? substr($headerInfo['childOrder'], -1, 1) : $v;
                                        return $v;
                                    },
                                    'dataCell' => function ($value, $cellInfo) {
                                        if ($cellInfo["fieldName"] === "perf_sum_w") {
                                            $cellInfo["formattedValue"] = $value . "%";
                                            // }else if ($cellInfo["fieldName"] === "hours"){
                                            // $cellInfo["formattedValue"] => round($value,0);
                                        }
                                        return $cellInfo["formattedValue"];
                                    }
                                ),
                                'hideTotalRow' => true,
                                'hideTotalColumn' => true,
                                'hideSubtotalRow' => true,
                                'hideSubtotalColumn' => true,
                                   'showDataHeaders'=>true,
                                'rowCollapseLevels' => $this->params['expand'] == 2 || (isset($_POST['expand1']) &&
                                    $_POST['expand1'] == 2) ?
                                    array(7) : array(1),

                                'headerMap' => array(
                                    'perf_name' => ($language == 'en' ? $transtable->where('key_name', 'perf_name')->get(0, "value_en") : $transtable->where('key_name', 'perf_name')->get(0, "value_ar")),
                                    'dept_name' => ($language == 'en' ? $transtable->where('key_name', 'dept_name')->get(0, "value_en") : $transtable->where('key_name', 'dept_name')->get(0, "value_ar")),//'Symbol',
                                    'sector_name' => ($language == 'en' ? $transtable->where('key_name', 'sector')->get(0, "value_en") : $transtable->where('key_name', 'sector')->get(0, "value_ar")),//'Next Reading Date',
                                    'sub_name' => ($language == 'en' ? $transtable->where('key_name', 'org_unit')->get(0, "value_en") : $transtable->where('key_name', 'org_unit')->get(0, "value_ar")),//'Org Unit',
                                    // 'id' => '#',//'Sequence',
                                    'perf_sum_w'=>($language == 'en' ? $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_ar"))//($language == 'en' ? $transtable->where('key_name', 'perf_sum_w')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w')->get(0, "value_ar")),//'KPI Name',

                                    // 'perf_sum_w1 - sum'=>($language == 'en' ?$transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_en") : $transtable->where('key_name', 'perf_sum_w1-sum')->get(0, "value_ar")),//'KPI Name',
                                ),
                                'cssClass' => array(
                                    'rowHeader' => function ($value, $cellInfo) {

                                        if (($cellInfo['fieldName'] === "id")  || ($cellInfo['fieldName'] === "sub_name") || ($cellInfo['fieldName'] === "perf_sum_w"))
                                            // <script>$(this).find('.fa-circle').removeClass('fa-circle')</script>

                                            return "fa1";
                                    }
                                ),
                            ));
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="" id="piechart" style="text-align:center;">
                    <h5><b><?php echo $this->params['language'] == 'en' ?'Unit Performing/Non Performing Ratio' : 'مخطط نسب الوحدات المؤدية/غير المؤدية';?></b></h5>
                    <?php
                    $p= $this->params['language'] == 'en' ?'Performing' : 'مؤدي';
                    $np=$this->params['language'] == 'en' ? 'Not Performing' : 'غير مؤدي';

                    $performing_count = $this->dataStore("performing1pie")->count();
                    $nonperforming_count = $this->dataStore("nonperforming1pie")->count();
                    \koolreport\d3\PieChart::create(array(
                        "dataStore" => array(
                            array("category" => $p, "count" => $performing_count),
                            array("category" => $np, "count" => $nonperforming_count),
                        ),
                        "colorScheme" => array(
                            "#01996d",
                            "#cc043e",
                        ),
                        "columns" => array(
                            "category" => array(
                                "cssStyle" => "white-space:nowrap"
                            ),
                            "count" => array(
                                "type" => "number",
                                "config" => array(
                                    "backgroundColor" => array("#0475CC", "#cc043e")
                                    //"backgroundColor"=>,
                                )
                                //"prefix"=>"$",
                            )
                        ),
                        "label" => array(
                            "use" => "ratio",
                        ),
                        "tooltip" => array(
                            "use" => "value",
                            //    "prefix"=>"$"
                        ),
                        //    "options"=>array(
                        //     "legend"=>array(
                        //     "position"=>'top',
                        //     "maxLines"=>1,
                        //     "itemWrap"=>false,
                        //     // "alignment"=>'center' ,
                        //     // "orientation"=>'horizontal',
                        //     "textStyle"=>array("color"=>'#717171'),

                        //     ),

                        // "backgroundColor"=>'transparent',
                        //     "chartArea"=>array('left'=>"10",'width'=>"100%" ),
                        //      "pieSliceText"=> 'value-and-percentage',
                        //      "colors"=>[
                        //         "#0475CC",
                        //         "#cc043e",

                        //       ],

                        // )
                        "options" => array(
                            "legend" => array(
                                "position" => "bottom", // Accept "bottom", "right", "inset"
                                "show" => false,
                            ),
                            "pieSliceText" => 'value',
                        )

                    ));
                    ?>
                </div>
                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-8">
                        <div class="decoratedLine1 vlabelBold <?php echo ($language=='en' ? 'decoratedLine1En' :
                            'decoratedLine1Ar') ?>" style="<?php echo ($language=='en' ? 'padding-left: 40px;' : 'padding-right:
                        40px;') ?>"><span><?php echo $p ?></span></div>
                    </div>
                    <div class="col-sm-2"></div>
                </div>
                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-8">
                        <div class="decoratedLine2 vlabelBold <?php echo ($language=='en' ? 'decoratedLine2En' : 'decoratedLine2Ar')?>" style="<?php echo ($language=='en' ? 'padding-left: 40px;' :
                            'padding-right: 40px;') ?>"><span><?php echo $np; ?></span></div>
                    </div>
                    <div class="col-sm-2"></div>
                </div>
                <br>
                <hr>
                <div class="">
                    <h5 style="text-align:center;"><b><?php echo $this->params['language'] == 'en' ?'Unit Distribution Graph' : 'مخطط توزيع الوحدات ';?></b></h5>
                    <?php
                    if(($this->dataStore("performing11")->countData()>0)||($this->dataStore("nonperforming11")->countData()>0))
                    {
                    
                        $p= $this->params['language'] == 'en' ?'Performing' : 'مؤدي';
                    $np=$this->params['language'] == 'en' ? 'Not Performing' : 'غير مؤدي';
                    // if(($newArray[0]=='sub_name')&&($newArray[1]=='kpi_perf')&&($newArray[2]=='nonperf_sum_w'))
                    // if($performing2)
                    \koolreport\d3\ColumnChart::create(array(
                        // "title"=>"Sale Report",
                        "dataSource" => $output,
                        // "dataSource"=>array(
                        //     array("category"=>"Org_unit1","performing"=>32,"nonperforming"=>20),
                        //     array("category"=>"Org_unit2","performing"=>42,"nonperforming"=>10),
                        //     array("category"=>"Org_unit3","performing"=>52,"nonperforming"=>15),
                        // ),
                        "colorScheme" => array(
                            "#01996d",
                            "#cc043e",


                        ),
                        "columns"=>array(
                            // "dept_name",
                            (isset($_POST['sector']))&&(!empty($_POST['sector']))?($this->params['section']!=""?'sub_name':'dept_name'):'sector_name',
                            "perf_sum_w"=>array("label"=>$p,"type"=>"number","formatValue"=>function($value){return $value."%";}),
                            "nonperf_sum_w"=>array("label"=>$np,"type"=>"number","formatValue"=>function($value){return $value."%";}),
                        ),
                        "options" => array(
                            "legend" => array(
                                "position" => "bottom", // Accept "bottom", "right", "inset"
                                "show" => false,
                            ),
                            "vAxis"=>array(
                                "format"=>'percent'
                                // 'showTextEvery' => 1,
                            ),
                            // "scales"=>array(
                            //     "yAxes"=>array(
                            //         array(
                            //             "tick"=>array(
                            //                 "autoSkip"=>false
                            //             )
                            //         )
                            //     )
                            // )

                        )
                    ));
                }
            
                    ?>
                </div>
                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-8">
                        <div class="decoratedLine1 vlabelBold <?php echo ($language=='en' ? 'decoratedLine1En' :
                            'decoratedLine1Ar') ?>" style="<?php echo ($language=='en' ? 'padding-left: 40px;' : 'padding-right:
                        40px;') ?>"><span><?php echo $p ?></span></div>
                    </div>
                    <div class="col-sm-2"></div>
                </div>
                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-8">
                        <div class="decoratedLine2 vlabelBold <?php echo ($language=='en' ? 'decoratedLine2En' : 'decoratedLine2Ar')?>" style="<?php echo ($language=='en' ? 'padding-left: 40px;' :
                            'padding-right: 40px;') ?>"><span><?php echo $np; ?></span></div>
                    </div>
                    <div class="col-sm-2"></div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-row">
            <div class="col-md-8">
                <div class="col-md-1">
                    &nbsp;&nbsp;
                </div>
                <div class="col-md-3">
                    <div class="flex-row">
                    </div>
                    <div class="row">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .pivot-row-header {
            width: 220px;
        }
    </style>
    <script type="text/javascript">
        function myFunction() {
            var x = document.getElementById("myDIV");
            if (x.style.display === "none") {
                $(x).show('slow');
            } else {
                $(x).hide('slow');
            }
        };

        function printDiv() {
            var divElements = document.getElementById('performance').innerHTML;
            var heading = "تقرير أداء  الوحدات"
            var siz = "";
            var test = '<?php echo $language;?>';
            if (test == 'en') {
                lang = 'ltr';
            } else {
                lang = 'rtl';
            }
            $("canvas").wrap("<div></div>");
            data2 = "<div style=\"text-align:center;font-size:9px;border: 0px !important;\"><h4>" + heading + "</h4>" + divElements + "</div>";
            console.log(data2);

            document.getElementById('printarea').style.display = "block";

            document.getElementById('printarea').innerHTML = data2;
            document.getElementById('printissue').style.display = "none";

            window.print(); // call print
            document.getElementById('printissue').style.display = "block";
            document.getElementById('printarea').style.display = "none";


        }

        function expandPivot() {
            $('#form2').submit();

        }

        KoolReport.load.onDone(function () {
            // var s=$('#inactive');
            $("#performing").on('change', function () {
                $('#form1').submit();
                console.log(s.val());

            });

            $("#nonperforming").on('change', function () {
                $('#form1').submit();
                console.log(s.val());

            });
        });

    </script>

</div>
<style>
    .decoratedLine1, .decoratedLine2, .decoratedLine3, .decoratedLine4, .decoratedLine5 {
        overflow: hidden;
    }

    .decoratedLine1>span, .decoratedLine2>span, .decoratedLine3>span, .decoratedLine4>span, .decoratedLine5>span {
        position: relative;
        display: inline-block;
    }

    .decoratedLine1>span:before, .decoratedLine2>span:before, .decoratedLine3>span:before, .decoratedLine4>span:before, .decoratedLine5>span:before{
        content: '';
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 591px;
        margin: 0 20px;
    }
    .decoratedLine1>span:before {
        border-bottom: 12px solid #01996d;
    }
    .decoratedLine2>span:before {
        border-bottom: 12px solid #cc043e;
    }
    .decoratedLine1En>span:before, .decoratedLine2En>span:before, .decoratedLine3En>span:before,.decoratedLine4En>span:before, .decoratedLine5En>span:before {
        right: 100%;
    }
    .decoratedLine1Ar>span:before, .decoratedLine2Ar>span:before, .decoratedLine3Ar>span:before,.decoratedLine4Ar>span:before, .decoratedLine5Ar>span:before {
        left: 100%;
    }
</style>
</body>
</html>

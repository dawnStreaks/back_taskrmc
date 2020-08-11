<?php

use \koolreport\widgets\koolphp\Table;
use \koolreport\processes\CalculatedColumn;
use \koolreport\inputs\BSelect;
use \koolreport\inputs\Select;
use \koolreport\processes\Sort;
use \koolreport\inputs\Select2;
use \koolreport\datagrid\DataTables;
use \koolreport\sparklines;
use \koolreport\inputs\DateTimePicker;
use \koolreport\inputs\CheckBoxList;
use Modules\ClientApp\Reports\KpiStatusReport;

$language = '';
if (isset($this->params['language']) && !empty($this->params['language'])) {
    $language = $this->params['language'];
}
// $debug_modeprog=false;
// $debug_modeperf=true;
// $sect = '';
// if (isset($this->params['sect']) && !empty($this->params['sect'])) {
//     $sect = $this->params['sect'];
// }
// var_dump($sect);
$sector_name = $this->dataStore('sector_name');

$sector11 = $sector_name->get(0, "name");
function get_text($textbit, $language)
{
    $this1 = new KpiStatusReport();
    $transtable = $this1->dataStore('translation');


    $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
    return $translation;
}


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
    <title>بيان حالة المؤشرات
    </title>
    <link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.7.0/css/all.css'
          integrity='sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ' crossorigin='anonymous'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>

</head>
<style>
    .buttons-print {
        background-color: #ffffff;
        boder: none;
    }

    table {
        width: 100%;
        table-layout: fixed;
    }
  
  
    .color {
        border: 0px solid black;
    }

    .insideBorder {
        border: 10px solid white;
    }

    .line {
        width: 1320px;
        border-bottom: 1px solid black;
        position: absolute;
    }

    .dataTables_filter input {
        width: 450px
    }

    .cssHeader {
        background-color: #73818f;
        color: #fff;
        text-align: <?php echo $language=='ar'?'right':'left' ;?>;
        font-size: 12px;
    }

    .cssItem {
        background-color: #fdffe8;
        font-size: 12px;
    }

    .container {
        display: flex;
        width: 100%;
        padding: 0px 0px;
        background: #fff;
    }

    .container1 {
        display: inline-flex;
        column-width: 100%;
        padding: 0px 0px;
        background: #fff;
    }

    .header img {
        float: left;
        width: 100px;
        height: 100px;
        background: #555;
    }

    .select {
        margin: 10px 10px 0px 325px;
    }

    .checkbox input[type="checkbox"]
        /* input[type="checkbox"], .checkbox-inline input[type="checkbox"], .radio input[type="radio"], .radio-inline input[type="radio"]{ */
    {
        position: relative;
        margin-left: 0px;
        float: right;
        font-weight: 700;
    }

    .checkbox label {

        /* float:right; */
        font-weight: 300;
        text-align: left;
        padding: 10px;
        /* margin-top:10px; */
        margin-bottom: 10px;
        /* margin-left:20px; */

    }

    /* #example_wrapper{
        padding-right:0px;
        padding-left:0px;
    } */
</style>
<body>
<div style="background-color:#ffffff;margin-left:10px;margin-right:5px;margin-top:0px;margin-bottom:30px;padding-top:30px;padding-right:10px;padding-left:10px;">
    <h4 class="mb-0 pt-2" style="text-align:center;color: #20a8d8;font-size: 20px;font-weight: normal;">بيان
        حالة المؤشرات
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
        <!-- <div class="col-md-10javascript:history.go(-1)"></div> -->
     <?php   if (isset($this->params['sect']) && !empty($this->params['sect']) && empty($_POST['sector']) &&  $this->params['sect']!="null" && $this->params['sect']!="undefined") ?>
        <span id="backlink1" style="display:<?php echo $this->params['back']==1?'block':'none'; ?>">
        <button onClick="linktokpi();" style="float:left;border:none;background-color: #ffffff;"><i
                    style="padding-top:10px;color:#a9a9a9;font-size:12px;" class="fa fa-arrow-left "></i></button>
        </span>
        <div id="button1" dir="rtl" style="float:<?php echo $lin; ?>">
            <button onclick="myFunction();" style="float:left;border:none;background-color: #ffffff;"><i
                        style="padding-top:5px;color:#a9a9a9;;" class="fa fa-angle-up 4x"></i></button>
        </div>
    </div>
    <br/>
    <?php $new = $this->dataStore('user_details') ?>
    <?php $style = "";
    if (empty($_POST)) {
        $style = 'display:none !important;';
    } else {
        $style = 'display:block !important;';
    } ?>

    <div class="col-md-12" id="myDIV"
         style="overflow:auto;width:100%;background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:30px;padding-right:30px;<?php echo $style; ?>">
        <form id="form1" method="post">
            <div class="col-md-3 form-group" style="float: right">
                <strong> <?php echo get_text('sector', $language) ?></strong>
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
                    "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong>
                    <!-- القسم -->
                    <?php echo get_text('org_unit', $language); ?>
                    <!-- section -->
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
                    "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong>
                    <?php echo get_text('mtp', $language); ?>
                    <!-- الخطة متوسطة الأجل -->
                    <!-- mtp -->
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
                    ), "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong>
                    <?php echo get_text('periodicity', $language); ?>

                </strong>
                <?php
                select2::create(array(
                    // "multiple"=>true,
                    "name" => "periodicity",
                    // "defaultOption" => array("--" => ""),
                    // "dataStore"=>$this->src("mysql")->query("select distinct name FROM supervision $sql"),
                    "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                    "data" => array(
                        get_text('quarter', $language) => 3, //"quarter"=>3,
                        get_text('semi_annual', $language) => 6,//"semi_annual"=>6,
                        get_text('annual', $language) => 12,//"annual"=>12,
                        get_text('every_3_years', $language) => 36,//"every_3_years"=>36,
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong><?php echo get_text('kpicat', $language); ?></strong>
                <?php
                select2::create(array(
                    // "multiple"=>true,
                    "name" => "kpi_category",
                    // "placeholder"=>"اختر ",
                    "defaultOption" => array("--" => ""),
                    /* "dataStore"=>$this->dataStore("category1"),
                     "dataBind"=>array(
                         "text"=>"name",
                         "value"=>"id",
                     ),*/
                    "data" => array(
                        "مثال 1 لتصنيف المؤشر" => 1,//sample_1_kpi_cat_tn1,
                        "مثال 2 لتصنيف المؤشر" => 2,//sample_1_kpi_cat_tn2,
                    ),
                    "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong>                <?php echo get_text('active_status', $language); ?>
                    <!-- kpi_activation_status -->
                </strong>
                <?php
                select2::create(array(
                    // "multiple"=>true,
                    "name" => "kpi_activation_status",
                    "defaultOption" => array("--" => ""),
                    // "dataStore"=>$this->src("mysql")->query("select distinct name FROM supervision $sql"),
                    "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                    "data" => array(
                        get_text('inactive', $language) => 0,//"Not Active"=>0,
                        get_text('active', $language) => 1// "Active"=>1,
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong><?php echo get_text('status', $language); ?>
                    <!-- kpi_status -->
                </strong>
                <?php
                select2::create(array(
                    // "multiple"=>true,
                    "name" => "kpi_status",
                    "defaultOption" => array("--" => ""),
                    "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                    "data" => array(
                        // ($language=='en'?$transtable->where('key_name','target_date')->get(0,"value_en"):$transtable->where('key_name','target_date')->get(0,"value_ar"))
                        //"في انتظار الموافقة" => 0,//"Pending for approval"=>0,
                        get_text('min allowed or less', $language) => get_text('min allowed or less', $language),
                        get_text('attempt to target', $language) => get_text('attempt to target', $language),
                        get_text('target achieved', $language) => get_text('target achieved', $language),
                        get_text('improved result', $language) => get_text('improved result', $language),
                        get_text('result(s) miss planning', $language) => get_text('result(s) miss planning', $language),

                        //"موافق عليه" => 1,//"Approved"=>1,
                        // "مرفوض" => 2,//"Rejected"=>2,
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right" dir=<?php $language == 'ar' ? "rtl" : "ltr"; ?>>
                <?php
                CheckBoxList::create(array(
                    "name" => "have_task",
                    "data" => array(
                        get_text('filter next value date less than today', $language) => 1

                    ),
                    "clientEvents" => array(
                        "change" => "function(params){
                            var table = $('#example').DataTable();
                            if(params.value==1){
                                $.fn.dataTable.ext.search.push(
                                    function(settings, data, dataIndex) {
                                    var test=new Date();
                                    console.log(test);
                                    var value1 = new Date( data[17] );
                                    console.log(value1);
                                    if(value1<test)
                                    {
                                        return true;
                                    }
                                    else {
                                        return false;
                                    }
                                });
                                table.draw();
                                $.fn.dataTable.ext.search.pop();
                            }
                            else{
                                table.draw();
                            }
                        }"
                    ),
                ));
                ?>
            </div>
            <br>
        </form>
    </div>
    <br/>
    <br/>
    <!-- <div class="card"> -->
    <div style="overflow:auto;width:100%;background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:10px;padding-right:10px;">
        <br>
        <br>
        <br>
        <?php
        DataTables::create(array(
            "dataSource" => $new,
            "name" => "example",
            "columns" => array(
                "kpi_symbol" => array(
                    "label" => get_text('symbol', $language)
                    // "رمز",
                ),
                "kpi_name" => array(
                    "label" => get_text('kpi_name', $language),
                    //"الاسم",
                    "formatValue" => function ($value, $row) {
                        $language1 = $this->params['language'];
                        $mtp1 = $this->params['mtp'];
                        $kpi1 = $row['kpi_id'];
                        $org_unit1 = $row['sub_name'];
                        //var_dump($row['kpi_symbol']);
                        $kpi_symbol1 = $row['kpi_symbol'];
                        $kpi_name1 = $row['kpi_name'];
                        $value_type1 = $row['value_type'];
                        // $org_unit1=
//                         <?php
                        $Mixed = array("kpi" => $kpi1, "mtp" => $mtp1, "org_unit" => $org_unit1, "kpi_symbol" => $kpi_symbol1, "kpi_name" => $kpi_name1, "value_type" => $value_type1);
                        // $url=$language1."/KpiValuesReport/".$mtp1."/".$row['kpi_id']."/".$row['kpi_symbol'];
                        $url = $language1 . "/KpiValuesReport/" . http_build_query(Array(
                                "array" => $Mixed
                            ));

                        return "<a href=" . $url . ">$value</a>";
                    }
                ),
                "sub_name" => array(
                    "label" => get_text('subtenant', $language)
                    //"org unit",
                ),
                "kpi_status_legend" => array(
                    "label" => get_text('status', $language),//"kpi status legend",
                    "formatValue" => function ($value, $row) {
                        $language = $this->params['language'];
                        $target = $row['target_value'];
                        $actual = $row['acc_value'];
                        $min_allowed = ($row['target_value'] != null) ? (($row['target_value']) * (1 - $row['margin_pct'])) : 0;
                        //($row['target_value'] != null)) ? (($row['target_value']) * (1 - $row['margin_pct'])) : 0
                        $improved = ($row['target_value'] != null) ? ($row['target_value'] * (1 + $row['margin_pct'])) : 0;


                        $color1 = '';
                        if ($row['v_exp_id'] == 1 || $row['v_exp_id'] == 2 || $row['v_exp_id'] == 3) {
                            $color1 = ($actual == null ? "#FFF" :
                                ($actual >= $min_allowed && $actual <= $target ? "#01996d" :
                                    ($target < $actual && $actual <= $improved ? "#3e98a3" :
                                        ($improved < $actual ? "#065a9b" :
                                            (($target + $min_allowed) / 2 <= $actual && $actual < $min_allowed ? "#ffa500" :
                                                ($actual < ($target + $min_allowed) / 2 ? "#cc043e" : ""))))));
                        } else if ($row['v_exp_id'] == 11 || $row['v_exp_id'] == 12 || $row['v_exp_id'] == 13) {
                            $color1 = ($actual == null ? "#FFF" :
                                ($target <= $actual && $actual <= $improved ? "#01996d" :
                                    ($min_allowed <= $actual && $actual < $target ? "#3e98a3" :
                                        ($improved < $actual ? "#065a9b" :
                                            (($target + $min_allowed) / 2 <= $actual && $actual < $min_allowed ? "#ffa500" :
                                                ($actual < ($target + $min_allowed) / 2 ? "#cc043e" : ""))))));
                        } else if ($row['v_exp_id'] == 4) {
                            $color1 = ($actual == null ? "#FFF" :
                                ($target <= $actual ? "#01996d" :
                                    ($actual < $target ? "#cc043e" : "")));
                        } else if ($row['v_exp_id'] == 14) {
                            $color1 = ($actual == null ? "#FFF" :
                                ($actual <= $target ? "#01996d" :
                                    ($target < $actual ? "#cc043e" : "")));
                        }
                        $status = ($color1 == '#cc043e' ? get_text('min allowed or less', $language) : ($color1 == '#ffa500' ? get_text('attempt to target', $language) : ($color1 == '#01996d' ? get_text('target achieved', $language) : ($color1 == '#3e98a3' ? get_text('improved result', $language) : ($color1 == '#065a9b' ? get_text('result(s) miss planning', $language) : null)))));
                        return "<div> <i style=\"color:$color1;\" class=\"fas fa-flag\"></i>&nbsp;&nbsp;" . $status . "&nbsp;</div>";
                    }
                ),
                "value_type" => array(
                    "label" => get_text('value_type', $language), //"تاريخ القراءة التالية",
                    "formatValue" => function ($value, $row) {
                        $language = $this->params['language'];
                        if ($value == 1) {
                            $term = get_text('number', $language);
                            return "<div>$term</div>";
                        }
                        if ($value == 2) {
                            $term = get_text('percentage', $language);
                            return "<div>$term</div>";
                        }
                        if ($value == 3) {
                            $term = get_text('ratio', $language);
                            return "<div>$term</div>";
                        }
                        if ($value == 4) {
                            $term = get_text('rate', $language);
                            return "<div>$term</div>";
                        }
                    }
                    //  "Next Value Date"  ,
                ),
                "u_comm_name" => array(
                    "label" => get_text('user_of_contact', $language),//"communication officer",
                    "cssStyle" => "overflow-wrap:beak-word;"

                ),
                "u_coord_name" => array(
                    "label" => get_text('user_of_coordination', $language), //"coordinator officer",
                    "cssStyle" => "overflow-wrap:beak-word;"
                ),
                "scope_table" => array(
                    "label" => get_text('scope_table', $language),//"kpi_type"
                ),
                "importance" => array(
                    "label" => get_text('importance', $language),//"kpi_importance"
                    "formatValue" => function ($value, $row) {
                        if ($value == 1)
                            return "<div>منخفض </div>";
                        if ($value == 2)
                            return "<div>متوسط </div>";
                        if ($value == 3)
                            return "<div>عالي </div>";
                    }
                ),
                // "value_type" => array(
                //     "label" =>get_text('value_type',$language),
                // ),
                "unit_name" => array(
                    "label" => get_text('kpi_unit_name', $language), //"تاريخ القراءة التالية",
                    //  "Next Value Date"  ,
                ),
                "numerator_name" => array(
                    "label" => get_text('formula', $language),//"معادلة احتساب المؤشر",
                    //"formula",
                    "formatValue" => function ($value, $row) {
                        if ($row['value_type'] == 2)
                            return "<div class='text-center'>" . $row['numerator_name'] . " &nbsp;/ &nbsp;" . $row['denominator_name'] . "  </div>";
                    }
                ),
                "base_value" => array(
                    "label" => get_text('base_value', $language),//"base"
                    'formatValue' => function ($val, $row) {
                        if ($val != null) {
                            $data = number_format((float)($val));
                            return "<div>$data</div>";
                        }
                    }
                ),
                "target_value" => array(
                    "label" => get_text('target', $language),//"المستهدف",
                    'formatValue' => function ($val, $row) {
                        if ($val != null) {
                            $data = number_format((float)($val));
                            return "<div>$data</div>";
                        }
                    }
                ),
                "acc_value" => array(
                    "label" => get_text('actual', $language),// "القراءة",
                    "cssStyle" => "overflow-wrap:beak-word;",
                    'formatValue' => function ($val, $row) {
                        if ($val != null) {
                            $data = number_format((float)($val));
                            return "<div>$data</div>";
                        }
                    }
                ),
                "to_target" => array(
                    "label" => get_text('target_diff', $language),// "القيمة إلى المستهدف",
                    'formatValue' => function ($val, $row) {
                        $language = $this->params['language'];
                        $lin = $language == 'en' ? 'left' : 'right';
                        $target = $row['target_value'];
                        $actual = $row['acc_value'];
                        $data = "";
                        if ($actual != null && $target != null) {
                            $data = ($target - $actual);
                            if ($data != null) {
                                $data = number_format((float)($data));
                                return "<div dir=\"ltr\" style=\"text-align:$lin\">$data %</div>";
                            }
                        }
                        // return "<div dir=\"ltr\" style=\"text-align:$lin\">$data &nbsp;%</div>";
                    }
                ),
                "performance_formula" => array(
                    "label" => get_text('kpi performance', $language),//"الأداء",
                    // "Performance"  ,
                    //"cssStyle"=>"overflow-wrap:beak-word;",
                    // "prefix"=>"%",
                    "type" => "number",
                    "decimals" => 2,
                    'formatValue' => function ($val, $row) {
                        $language = $this->params['language'];
                        $lin = $language == 'en' ? 'left' : 'right';
                        $target = $row['target_value'];
                        $mn = ($row['min_value']) ? $row['min_value'] : 0;
                        $mx = ($row['max_value']) ? $row['max_value'] : $target * 1.2;
                        $value = $row['acc_value'];
                        $factor_1 = $row['perf_factor_1'];
                        $factor_2 = $row['perf_factor_2'];
                        $base = $row['base_value'];
                        $data = ($mx == $mn) ? 0 : eval("return " . $val . ";");
                        $data = $data * 100;
                        $data = round($data, 2);
                        if ($data != null) {
                            $data = number_format(((float)($data)), 2);
                            //   $data=(float)$data;
			if($this->params['debug_modeperf']==true && $data>100)
				$data=100;

                            return "<div dir=\"ltr\" style=\"text-align:$lin\">$data %</div>";
                        }
                    }
                ),
                "progress_value" => array(
                    "label" => get_text('progress', $language),//"التقدم",
                    "type" => "number",
                    "decimals" => 2,
                    'formatValue' => function ($val, $row) {
                        $language = $this->params['language'];
                        $lin = $language == 'en' ? 'left' : 'right';
                        $target = $row['target_value'];
                        $actual = $row['acc_value'];
                        $val = $row['base_value'];
                        $data = "";
                        if (($actual != null) && ($target != null))
                            $data = ($val == $target) ? null : (($actual - $val) / ($target - $val)) * 100;
                        if ($data != null) {
                            $data = round($data, 2);
                            $data = number_format(((float)($data)), 2);
			if($this->params['debug_modeprog']==false && $data<0)
 			    $data=0;
                            return "<div dir=\"ltr\" style=\"text-align:$lin\">$data %</div>";
                        }
                    }
                ),
                "next_reading_date" => array(
                    "label" => get_text('upcoming_value_date', $language), //"تاريخ القراءة التالية",
                    //  "Next Value Date"  ,
                ),
            ),
            "cssClass" => array(
                "table" => "table table-striped table-bordered color  ",
                "th" => "cssHeader insideBorder ",
                "tr" => "cssItem color",
                "td" => "insideBorder"
            ),
            "options" => array(
                "columnDefs" => array(
                    array("width" => 50, "targets" => 0),
                    array("width" => 120, "targets" => 1),
                    array("width" => 100, "targets" => 2),
                    array("width" => 80, "targets" => 3),
                    array("width" => 50, "targets" => 4),
                    array("width" => 60, "targets" => 5),
                    array("width" => 60, "targets" => 6),
                    array("width" => 50, "targets" => 7),
                    array("width" => 50, "targets" => 8),
                    array("width" => 30, "targets" => 9),
                    array("width" => 60, "targets" => 10),
                    array("width" => 30, "targets" => 11),
                    array("width" => 30, "targets" => 12),
                    array("width" => 30, "targets" => 13),
                    array("width" => 40, "targets" => 14),
                    array("width" => 40, "targets" => 15),
                    array("width" => 50, "targets" => 16),
                    array("width" => 50, "targets" => 17),
                ),
                "searching" => true,
                "paging" => true,
                "orders" => array(
                    array(0, "asc")
                )
            )
        )); ?>
    </div>

    <script type="text/javascript">
        KoolReport.load.onDone(function () {
            var locale = '<?php echo $language;?>';

            var table = $('#example').DataTable({
                destroy: true,
                "pageLength": 50,
                "language": {
                    "sProcessing": locale == 'ar' ? "جارٍ التحميل..." : "Processing...",
                    "sLengthMenu": locale == 'ar' ? "اعرض _MENU_ سجلات" : "Show _MENU_ entries",
                    "sZeroRecords": locale == 'ar' ? "لم يعثر على أية سجلات" : "No matching records found",
                    "sInfo": locale == 'ar' ? "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل" : "Showing _START_ to _END_ of _TOTAL_ entries",
                    "sInfoEmpty": locale == 'ar' ? "يعرض 0 إلى 0 من أصل 0 سجل" : "Showing 0 to 0 of 0 entries",
                    "sInfoFiltered": locale == 'ar' ? "(منتقاة من مجموع _MAX_ مُدخل)" : "(filtered from _MAX_ total entries)",
                    "sInfoPostFix": "",
                    "sSearch": locale == 'ar' ? "ابحث:" : "Search:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": locale == 'ar' ? "الأول" : "First",
                        "sPrevious": locale == 'ar' ? "السابق" : "Last",
                        "sNext": locale == 'ar' ? "التالي" : "Next",
                        "sLast": locale == 'ar' ? "الأخير" : "Previous",
                    },
                    buttons: {
                        colvisRestore: locale == 'ar' ? " إعادة إظهار الخانات" : "restore"
                    }
                },
                "buttons": [
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-align-justify" style="color:#a9a9a9;"></i>',
                        postfixButtons: ['colvisRestore'],
                        titleAttr: 'إظهار/إخفاء الخانات',
                        columns: ':not(.noVis)',
                    },
                    {
                        extend: 'print',
                        text: '<i style="color:#a9a9a9;" class="fa fa-print"></i>',
                        titleAttr: 'طباعة',
                        autoPrint: true,
                        cssClass: 'printButton',
                        exportOptions: {
                            columns: ':visible',
                        },
                        customize: function (win) {
                            $(win.document.body).find('table').addClass('display').css('font-size', '9px');
                            $(win.document.body).find('table').addClass('display').css('direction', '<?php echo $language == 'en' ? "ltr" : "rtl";?>');
                            $(win.document.body).find('tr:nth-child(odd) td').each(function (index) {
                                $(this).css('background-color', '#D0D0D0');
                            });
                            $(win.document.body).find('h1').css('text-align', 'center');

                            $(win.document.body).prepend('<div style="text-align:center;"><?php echo $sector11; ?></div>'); //before the table
                            $(win.document.body).find('th').css('background-color', '#2f353a');
                            $(win.document.body).find('th').css('color', '#fff');
                        }
                    },
                ],
                // responsive: true,
                "columnDefs": [{
                    "searchable": true,
                    "orderable": true,
                    "targets": 0
                },
                    {
                        'visible': false,
                        'targets': 4,
                        // className: 'noVis'
                    },
                    {
                        'visible': false,
                        'targets': 5,
                        // className: 'noVis'
                    },
                    {
                        'visible': false,
                        'targets': 6,
                        // className: 'noVis'
                    },
                    {
                        'visible': false,
                        'targets': 7,
                        // className: 'noVis'
                    },
                    {
                        'visible': false,
                        'targets': 8,
                        // className: 'noVis'
                    },
                    {
                        'visible': false,
                        'targets': 10,
                        // className: 'noVis'
                    },
                    {
                        'visible': false,
                        'targets': 16,
                        // className: 'noVis'
                    },
                ], "order": [[0, 'asc']],
                initComplete: function () {
                    //                 $('#have_task').on('change', function(){
                    //             console.log("completecheck");
                    //             $.fn.dataTable.ext.search.push(
                    //                 function(settings, data, dataIndex) {

                    //                     var d=new Date();

                    //     month = '' + (d.getMonth() + 1),
                    //     day = '' + d.getDate(),
                    //     year = d.getFullYear();

                    // if (month.length < 2)
                    //     month = '0' + month;
                    // if (day.length < 2)
                    //     day = '0' + day;

                    // createDate=[year, month, day].join('-');

                    //                     var today1  =  createDate;
                    //                     console.log("complete");
                    //                     var spark = parseDate( data[17] );
                    //                     console.log(spark+"<="+complete+"----"+(spark<=complete));
                    //                     if(spark<=complete)
                    //                     {
                    //                         return true;
                    //                     }
                    //                     return false;
                    //                 }
                    //             );


                    //             table.draw();
                    //         });
                    this.api().column([3]).every(function () {
                        var column = this;
                        console.log("check");
                        var select = $('#kpi_status')
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );
                                console.log('^' + val + '$');
                                column
                                    .search(val ? val : '', true, false)
                                    .draw();
                            });
                    });
                    var s = $('#sector');
                    // var t=  $('#section').val();
                    // console.log(t);
                    s.on('change', function () {
                        // if(t!="")
                        // $('#section').val(t).trigger("change");
                        // else
                        $('#section').val(null).trigger("change");
                        $('#form1').submit();
                    });
                    $('#section').on('change', function () {
                        $('#form1').submit();
                    });
                    $('#mtp').on('change', function () {
                        $('#form1').submit();
                    });
                    $('#periodicity').on('change', function () {
                        $('#form1').submit();
                    });
                    $('#kpi_category').on('change', function () {
                        $('#form1').submit();
                    });
                    $('#kpi_activation_status').on('change', function () {
                        $('#form1').submit();
                    });
                    // $('#kpi_status').on('change', function () {
                    //     $('#form1').submit();
                    // });
                }
            });
            table.buttons().container().appendTo($('#button1'));
            /*table.on( 'order.dt search.dt', function () {
                table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            }).draw();*/
        });

        function myFunction() {
            var x = document.getElementById("myDIV");
            if (x.style.display === "none") {
                $(x).show('slow');
            } else {
                $(x).hide('slow');
            }
        };
        function linktokpi()
        {
        var m=$('#sector').val()
// alert(m);
        var n=$('#section').val()
console.log(n);

            // history.replaceState('data to be passed', 'NAJAH', '/kpilist');    

       // document.cookie = "sectorname="+m+"; path=/; ";
         document.cookie = "sectorname="+m+";domain=.najah.online; path=/; ";

       
        if(n!=null)

                document.cookie = "orgname="+n+";domain=.najah.online; path=/; ";

                        //document.cookie = "orgname="+n+"; path=/; ";

        //    top.window.location.href= "http://localhost:8080/kpilist";
          top.window.location.href= "http://dev.najah.online/kpilist";


            };
    </script>
    <style>
        .container1 {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.25rem;
            display: inline-flex;
            column-width: 100%;
            padding: 0px 0px;
            background: #fff;
        }

        .buttons-print {
            background-color: #ffffff;
            boder: none;
            /*color:black;*/
        }

        button.dt-button, div.dt-button, a.dt-button, a.dt-button:focus {
            border: none !important;
            background-color: #ffffff;
            background: none;
            padding: 0;
        }
        
        
        div.dt-button-collection {

        /* top: 19.6166px; */
        left: -98.433px;
        transform:translateX(-98px);
        }

        /* .card-body {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.25rem;
        } */
        .select2 {
            width: 100% !important;
            /*border: 1px solid #e8e8e8;*/
            min-height: 40px;
        }

        button.dt-button {
            font-size: 0.68em;
        }

        /* .select2:after{
            content: '';
            position:absolute;
            left:10px;
            top:15px;
            width:0;
            height:0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #888;
            direction: rtl;
        }; */
    </style>
</div>
</body>
</html>

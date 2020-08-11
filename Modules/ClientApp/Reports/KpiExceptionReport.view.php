<?php

// use \koolreport\widgets\koolphp\Table;
// use \koolreport\processes\CalculatedColumn;
use \koolreport\inputs\BSelect;
use \koolreport\inputs\Select;
use \koolreport\processes\Sort;
use \koolreport\inputs\Select2;
// use \koolreport\datagrid\DataTables;
// use \koolreport\sparklines;
// use \koolreport\inputs\DateTimePicker;
use \koolreport\inputs\CheckBoxList;
use \koolreport\pivot\widgets\PivotTable;
use Modules\ClientApp\Reports\KpiPivotReport;
use \koolreport\processes\Filter;
$language = '';
if (isset($this->params['language']) && !empty($this->params['language'])) {
    $language = $this->params['language'];
}
$sector_name = $this->dataStore('sector_name');

$sector11 = $sector_name->get(0, "name");
$transtable = $this->dataStore('translation');

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
    <title>اتساق بيانات المؤشرات
    </title>
    <!-- <link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.7.0/css/all.css'
          integrity='sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ' crossorigin='anonymous'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script> -->
    <!--  <script src="../../../public/koolreport_assets/font-awesome/css/font-awesome.min.css"></script> --> 
    <!-- <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">  -->

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css"></head> -->


<style>
/* .pivot-row-header-text{
    visibility:hidden !important;
} */
#form2{
display: inline;
}
/* .table-bordered >tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th{    border: 0px !important; */
     /* /* solid     #ddd;  */
/* }  */
.fa1 i{
    visibility:hidden !important;
    /* text-align: <\?php echo $language=='ar'?'right':'left' ;?>;  */

    /* opacity:0; */
}
.pivot-row-header-text{
   text-align: <?php echo $language=='ar'?'right':'left' ;?>; 

}
 
.table-bordered{
        border: 0px !important;
} 
.select2 {
            width: 100% !important;
            min-height: 40px;
        }
      
        @media print {
 .table{
            transform:<?php echo $language=='ar'?'scale(0.75) translate(140px,50px); ':"" ;?>; 
            
        }
        .pivot-row-header-text i{
    visibility:hidden !important;
        }

        .table td{
            padding-left:2px;
            padding-right:2px;
        }

       
      
        }
</style>
<body>
<p id="printarea"></p>
<div id="printissue">
<div style="background-color:#ffffff;margin-left:10px;margin-right:5px;margin-top:0px;margin-bottom:30px;padding-top:30px;padding-right:10px;padding-left:10px;">
    <h4 class="mb-0 pt-2" style="text-align:center;color: #20a8d8;font-size: 20px;font-weight: normal;">
    <?php echo $language=='ar'?'اتساق بيانات المؤشرات':'KPI Exception Report' ;?>   
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
        <button  onclick="myFunction()" style="float:left;border:none;background-color: #ffffff;"><i style="padding-top:5px;color:#a9a9a9;" class="fa fa-angle-up 4x" title="toggle"></i></button>
            <form id="form2" method="post">

            <button class="form-group" onClick="javascript:printDiv()" style="float:left;border:none;background-color: #ffffff;"><i style="padding-top:5px;color:#a9a9a9;font-size:12px" class="fa fa-print" title="print"></i></button>
             <!-- <button class="form-group" onClick="javascript:expandPivot()" name="expand" value=2 style="float:left;border:none;background-color: #ffffff;"><i style="padding-top:5px;color:#a9a9a9;font-size:12px" class="fa fa-plus-square" title="expand"></i></button> -->
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
    <?php $new = $this->dataStore('user_details') 
    
    ?>
    <?php $style = "";
    if((empty($_POST['sector']))&&(empty($_POST['section']))&&(empty($_POST['mtp'])) ) {
       $style = 'display:none !important;';
            //   $style = 'display:block !important;';

    } else {
        $style = 'display:block !important;';
            //    $style = 'display:none !important;';

    } ?>

    <div class="col-md-12" id="myDIV"
         style="overflow:auto;width:100%;background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:30px;padding-right:30px;<?php echo $style; ?>">
        <form id="form1" method="post">
        <!-- @method('PUT') -->
        <!-- <input type="hidden" id="expand1" name="expand1" value="<\?php echo $this->params["expand"]; ?>">  -->
        <?php $expVal = 0;
                if ($this->params['expand'] == 0) {
                    $expVal = $this->params['expand1'];
                } elseif ($this->params['expand1'] == 0) {
                    $expVal = $this->params['expand'];
                }
                ?>
                <input type="hidden" id="expand1" name="expand1" value="<?php echo $expVal; ?>">

        <div class="col-md-3 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
            <strong>
                        <?php $textbit = 'sector';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
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
                    //     "class" => "col-md-4 form-control"
                    // ),
                    "clientEvents" => array(
                        "change" => "function(params){
                            $('#section').val(null).trigger(\"change\");
                            $('#form1').submit();


                        }"
                    ),

                ));
                ?>
            </div>

           
                   <div class="col-md-3 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
               
                <strong>
                        <?php $textbit = 'org_unit';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
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

            
            <div class="col-md-3 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
            <strong>
                        <?php $textbit = 'mtp';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
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
                    //  "attributes" => array(
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
            <!-- <div class="col-md-3 form-group" style="float:<\?php echo $language == 'ar' ? "right" : "left"; ?>;">
            <strong>
                        <\?php $textbit = 'periodicity';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                <\?php
                select2::create(array(
                    // "multiple"=>true,
                    "name" => "periodicity",
                    // "defaultOption" => array("--" => ""),
                    // "dataStore"=>$this->src("mysql")->query("select distinct name FROM supervision $sql"),
                    // "attributes" => array(
                    //     "class" => "col-md-4 form-control"
                    // ),
                    "data" => array(
                        $language == 'en' ? $transtable->where('key_name', 'quarter')->get(0, "value_en") : $transtable->where('key_name', 'quarter')->get(0, "value_ar") => 3, //"quarter"=>3,
                        $language == 'en' ? $transtable->where('key_name', 'semi_annual')->get(0, "value_en") : $transtable->where('key_name', 'semi_annual')->get(0, "value_ar") => 6,//"semi_annual"=>6,
                        $language == 'en' ? $transtable->where('key_name', 'annual')->get(0, "value_en") : $transtable->where('key_name', 'annual')->get(0, "value_ar")=> 12,//"annual"=>1=> 36,//"every_3_years"=>36,
                        $language == 'en' ? $transtable->where('key_name', 'every_3_years')->get(0, "value_en") : $transtable->where('key_name', 'every_3_years')->get(0, "value_ar")
                    ),

                    "clientEvents" => array(
                        "change" => "function(params){
                            $('#form1').submit();


                        }"
                    ),
                ));
                ?>
            
            
          </div>    -->
               </form>
    </div>
    <br/>
    <br/>
    <div id="pivot" style="overflow:auto;width:100%;background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:10px;padding-right:10px;">
        

        <?php
            // $this->dataStore('user_details_en')->prepend(array("org_unit_name"=>"Org Unit","kpi_name"=>"Kpi Name","active_status"=>"Active Status","user_coord_name"=>"Coordinator Name","id"=>"Sequence","excecption_short_en"=>"Exception"));

         PivotTable::create(array(
            'dataStore'=> $language=='ar'?$this->datastore('user_details_ar'):$this->datastore('user_details') ,
            // "columnDimension"=>"column",
            "rowDimension"=>"row",
            'measures'=>array(
                // 'dollar_sales - sum', 
                'org_unit_name',
                'kpi_name',
                'active_status',
                'user_coord_name',
                'id',
                'excecption_short_en',
                // 'excecption_short_ar',

              ),
             'map' => array(
                'rowHeader' => function($rowHeader, $headerInfo) {
                    $v = $rowHeader;
                    if (isset($headerInfo['childOrder']))
                    $v = $headerInfo["fieldName"]=="id"?substr($headerInfo['childOrder'], -1, 1):$v;
                    return $v;
                }),
                'hideTotalRow' => true,
                'hideTotalColumn' => true,
                'hideSubtotalRow' => true,
                'hideSubtotalColumn' => true,
                'showDataHeaders'=>true,
                'rowCollapseLevels' =>$this->params['expand'] == 2 || (isset($_POST['expand1']) &&
                $_POST['expand1'] == 2)?array(7): array(0,1,3),
                'headerMap' => array(
                    'org_unit_name'=>($language == 'en' ? $transtable->where('key_name', 'org_unit')->get(0, "value_en") : $transtable->where('key_name', 'org_unit')->get(0, "value_ar")),//'Org Unit',
                    'kpi_name'=>($language == 'en' ? $transtable->where('key_name', 'kpi_name')->get(0, "value_en") : $transtable->where('key_name', 'kpi_name')->get(0, "value_ar")),//'Kpi Name',
                    'active_status'=>($language == 'en' ? $transtable->where('key_name', 'active_status')->get(0, "value_en") : $transtable->where('key_name', 'active_status')->get(0, "value_ar")),//'Active Status',
                    'user_coord_name'=>($language == 'en' ? $transtable->where('key_name', 'user_of_coordination')->get(0, "value_en") : $transtable->where('key_name', 'user_of_coordination')->get(0, "value_ar")),//'Coordinator Name',
                    'id'=>'#',//'Sequence',
                    'excecption_short_en'=>($language == 'en' ? $transtable->where('key_name', 'short_desc_en')->get(0, "value_en") : $transtable->where('key_name', 'short_desc_en')->get(0, "value_ar")),//,'Exception',
                    // 'excecption_short_ar'=>($language == 'en' ? $transtable->where('key_name', 'short_desc_ar')->get(0, "value_en") : $transtable->where('key_name', 'excecption_short_ar')->get(0, "value_ar")),//'Exception_ar',
                  
                ),
                // 'headerMap' => function($v, $f) {
                //     if ($v === 'org_unit_name')
                //         $v = 'Org Unit';
                //     if ($v === 'kpi_name')
                //         $v = 'KPI Name';
                //     // if ($f === 'Active Status')
                //     //     $v = 'Year' ;
                //     return $v;
                // },

               
            // ),
            'cssClass' => array(
                'rowHeader' => function($value, $cellInfo) {

                if(($cellInfo['fieldName']==="active_status_ar")||($cellInfo['fieldName']==="active_status_en")||($cellInfo['fieldName']==="user_coord_name")||($cellInfo['fieldName']==="id")||($cellInfo['fieldName']==="excecption_short_en")||($cellInfo['fieldName']==="excecption_short_ar"))
                    return "fa1";
                    }
            ),
           

         ));
        
       ?>
    </div>
    </div>
    <script type="text/javascript">
      
         function myFunction() {
            var x = document.getElementById("myDIV");
            if (x.style.display === "none") {
                $(x).show('slow');
            } else {
                $(x).hide('slow');
            }
        }
        function printDiv() {
            var divElements = document.getElementById('pivot').innerHTML;
            var heading = "بيان  حالة المؤشرات"
            var siz="";
            var test='<?php echo $language;?>' ;
            if(test=='en')
            {
                lang='ltr';
            }
            else{
                lang='rtl';
                // siz='scale(0.40) translate(800px,50px); ';
            }

           data2="<div style=\"text-align:center;font-size:8px;border: 0px !important;\"><h4>" + heading + "</h4>" +  divElements + "</div>";
            
  
    document.getElementById('printarea').style.display="block";
    document.getElementById('printarea').innerHTML=data2;
    document.getElementById('printissue').style.display="none";
    window.print(); // call print
    document.getElementById('printissue').style.display="block";
    document.getElementById('printarea').style.display="none";



               }

               function expandPivot()
               {
               
                 $('#form2').submit();
           

               }

    </script>
    
</div>
</body>
</html>
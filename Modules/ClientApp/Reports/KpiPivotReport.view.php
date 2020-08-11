<?php

use \koolreport\widgets\koolphp\Table;
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
// var_dump($_POST);
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
    <title>لوحة متابعة المؤشرات
    </title>
    <!-- <link rel='stylesheet' href='../../../assets/font-awesome/css/font-awesome.min.css'> -->
         <!-- <script src="../../../public/koolreport_assets/PivotTable.js"></script>  -->

<style>

/*.table-bordered >tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th{    border: 0px !important;
     /* /* solid     #ddd;  
} */
#form2{
display: inline;
}
.fa1 i{
    visibility:hidden !important;
    /* text-align: <\?php echo $language=='ar'?'right':'left' ;?>;  */

    /* opacity:0; */
}
.pivot-row-header-text{
   text-align: <?php echo $language=='ar'?'right':'left' ;?>; 

}
.pivot-data-field-zone {
        display: visible;
    }
 /* .pivot-row-header-total{
    border: 0px !important;
            }
        
    
            .pivot-column-header-total, .pivot-data-cell-column-total, .pivot-data-header-total {
                border: 0px !important;
            }*/

.table-bordered{
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
 .table{
            /* font-size: 50%; */
            transform:<?php echo $language=='ar'?'scale(0.7) translate(205px,50px); ':"" ;?>; 
             /* margin-right:20px;   
             padding-right:20px; */
        }
       table td{
            padding-left:5px;
            padding-right:5px;
        }
        .pivot-row-header-text i{
    visibility:hidden !important;
        }


        }
</style>
<body>
<p id="printarea"></p>
<div id="printissue">
<div style="background-color:#ffffff;margin-left:10px;margin-right:5px;margin-top:0px;margin-bottom:30px;padding-top:30px;padding-right:10px;padding-left:10px;">
    <h4 class="mb-0 pt-2" style="text-align:center;color: #20a8d8;font-size: 20px;font-weight: normal;">
    <?php echo $language=='ar'?'لوحة متابعة المؤشرات':'KPIs Late Reading Report' ;?>   



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
        <span id="backlink1" style="display:<?php echo $this->params['back']==1?'block':'none'; ?>">
        <button onClick="linktokpi();" style="float:left;border:none;background-color: #ffffff;"><i
                    style="padding-top:10px;color:#a9a9a9;font-size:12px;" class="fa fa-arrow-left "></i></button>
        </span>
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
    // var_dump
    ?>
    <?php $style = "";
    if (empty($_POST['sector'])&&(empty($_POST['section']))&&(empty($_POST['mtp']))&&(empty($_POST['periodicity']))&&(empty($_POST['kpi_category']))&&(empty($_POST['kpi_activation_status']))&&(empty($_POST['status1']))) {
    // if(empty($_POST['filter'])){
        $style = 'display:none !important;';
    } else {
        $style = 'display:block !important;';
    } ?>

    <div  id="myDIV" dir="<?php echo $language == 'ar' ? "rtl" : "ltr"; ?>"
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

        <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
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

           
                   <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
               
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

            <div class="col-md-4 form-group" style="float:<?php echo $language == 'ar' ? "right" : "left"; ?>;">
            <strong>
                        <?php $textbit = 'status';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                <?php
                select2::create(array(
                    // "multiple"=>true,
                    "name" => "status1",
                    "defaultOption" => array("--" => ""),
                    // "attributes" => array(
                    //     "class" => "col-md-4 form-control"
                    // ),
                    "data" => array(
                        "outdated"=>1,
                        "uptodate"=>2

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
                        <?php $textbit = 'periodicity';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                <?php
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
            
            
          </div>
                    </form>
    </div>
    
    <!-- <div class="card"> -->
    <div id="pivot" style="overflow:auto;width:100%;background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:10px;padding-right:10px;">
        
        
        <?php
        
        
         PivotTable::create(array(
            'dataStore'=>($this->params["status1"]==1?$this->datastore('outdated'):($this->params["status1"]==2?$this->datastore('updated'):$this->datastore('user_details'))),
            'rowDimension'=>'row',
            'measures'=>array(
                // 'dollar_sales - sum', 
                'u_comm_name',
                'u_comm_email',
                'u_comm_phone_internal',
                'sub_name',
                'kpi_symbol',
                'kpi_name',
                'next_reading_date',
              ),
            //   'map' => array(
            //     'dataHeader' => function($dataField, $fieldInfo) {
            //          $v = 1;//$fiel["fieldName"];
            //         return $v;
                
            //     }),

            'rowCollapseLevels' =>$this->params['expand'] == 2 || (isset($_POST['expand1']) && $_POST['expand1'] == 2)?array(7) : array(0,3),
            'hideTotalRow' => true,
            'hideTotalColumn' => true,
            'hideSubtotalRow' => true,
            'hideSubtotalColumn' => true,
            'showDataHeaders'=>true,
            'headerMap' => array(
                'u_comm_name'=>($language == 'en' ? $transtable->where('key_name', 'user_of_contact')->get(0, "value_en") : $transtable->where('key_name', 'user_of_contact')->get(0, "value_ar")),//'Communicator Name',
                'u_comm_email'=>($language == 'en' ? $transtable->where('key_name', 'contact_email')->get(0, "value_en") : $transtable->where('key_name', 'contact_email')->get(0, "value_ar")),//'Email',
                'u_comm_phone_internal'=>($language == 'en' ? $transtable->where('key_name', 'contact_phone_internal')->get(0, "value_en") : $transtable->where('key_name', 'contact_phone_internal')->get(0, "value_ar")),//'Phone',
                'sub_name'=>($language == 'en' ? $transtable->where('key_name', 'org_unit')->get(0, "value_en") : $transtable->where('key_name', 'org_unit')->get(0, "value_ar")),//'Org Unit',
                'kpi_symbol'=>($language == 'en' ? $transtable->where('key_name', 'kpi_symbol')->get(0, "value_en") : $transtable->where('key_name', 'kpi_symbol')->get(0, "value_ar")),//'Next Reading Date',
                'kpi_name'=>($language == 'en' ? $transtable->where('key_name', 'kpi_name')->get(0, "value_en") : $transtable->where('key_name', 'kpi_name')->get(0, "value_ar")),//'Symbol',
                'next_reading_date'=>($language == 'en' ? $transtable->where('key_name', 'upcoming_value_date')->get(0, "value_en") : $transtable->where('key_name', 'upcoming_value_date')->get(0, "value_ar")),//'KPI Name',
              
            ),
            
           
            'cssClass' => array(
                'rowHeader' => function($value, $cellInfo) {

                if(($cellInfo['fieldName']==="u_comm_email")||($cellInfo['fieldName']==="u_comm_phone_internal")||($cellInfo['fieldName']==="kpi_symbol")||($cellInfo['fieldName']==="kpi_name")||($cellInfo['fieldName']==="next_reading_date"))
                // <script>$(this).find('.fa-circle').removeClass('fa-circle')</script>
           
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
        };
//        
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

                // document.cookie = "orgname="+n+"; path=/; ";
// 
       
        top.window.location.href= "http://dev.najah.online/kpilist";

            // top.window.location.href= "http://localhost:8080/kpilist";

            };

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
            }

           data2="<div style=\"text-align:center;font-size:9px;border: 0px !important;\"><h4>" + heading + "</h4>" +  divElements + "</div>";
            
   
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
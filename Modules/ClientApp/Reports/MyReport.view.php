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

$language = '';
if (isset($this->params['language']) && !empty($this->params['language'])) {
    $language = $this->params['language'];
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<html>
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
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script> -->
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
    .cssHeader {
        background-color: #20a8d8;
        text-align: right;
    }
    .cssItem {
        background-color: #fdffe8;
    }
    .container {
        width: 100%;
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
</style>
<body>
<div align=center
     style="background-color:#ffffff;margin-left:30px;margin-right:30px;margin-top:0px;margin-bottom:30px;padding-top:30px;">
    <h4 class="mb-0 pt-2" style="color: #20a8d8;font-size: 20px;font-weight: normal;text-decoration: underline;">بيان
        حالة المؤشرات
        <?php echo "<script>get_text('org_unit@l')</script>" ?>
    </h4>
    <scan class="col-md-3 form-group" style="float: right">
        <script> document.write(new Date().toDateString()); </script>
    </scan>
    <div id="button1" style="float:right;border: 1px solid #a5aeb7;background-color:#ffffff;font-size:18px;"
         class="col-md-12">
        <button onclick="myFunction()" style="float:left;border:none;background-color: #ffffff;"><i
                    style="padding-top:10px;color:#a9a9a9;;" class="fa fa-angle-up 4x"></i></button>
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
         style="background-color:#ffffff;border: 1px solid #a5aeb7;padding-top:10px;margin-bottom:20px;border-radius: 0.25rem;<?php echo $style; ?>;">
        <form id="form1" method="post">
            <div class="col-md-3 form-group" style="float: right">
                <strong>القطاع
                    <!-- sector -->
                </strong>
                <?php
                select2::create(array(
                    // "multiple"=>false,
                    "name" => "sector",
                    "defaultOption" => array("--" => ""),
                    // "dataStore"=>$this->src("mysql")->query("select distinct name FROM supervision $sql"),
                    //  "dataStore"=>$this->src("mysql")->query("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3"),
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
                    القسم
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
                <strong>الخطة متوسطة الأجل
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
                <strong>دورية تسجيل القراءات
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
                        "ربع سنوي" => 3, //"quarter"=>3,
                        "نصف سنوي" => 6,//"semi_annual"=>6,
                        "سنوي" => 12,//"annual"=>12,
                        "كل 3 سنوات" => 36,//"every_3_years"=>36,
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong>تصنيف المؤشر
                </strong>
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
                <strong>حالة التفعيل
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
                        "غير مفعل" => 0,//"Not Active"=>0,
                        "مفعل" => 1// "Active"=>1,
                    ),
                ));
                ?>
            </div>
            <div class="col-md-3 form-group" style="float: right">
                <strong>حالة المؤشر
                    <!-- kpi_status -->
                </strong>
                <?php
                select2::create(array(
                    // "multiple"=>true,
                    "name" => "kpi_status",
                    "defaultOption" => array("--" => ""),
                    // "dataStore"=>$this->src("mysql")->query("select distinct name FROM supervision $sql"),
                    "attributes" => array(
                        "class" => "col-md-4 form-control"
                    ),
                    "data" => array(
                        "في انتظار الموافقة" => 0,//"Pending for approval"=>0,
                        "موافق عليه" => 1,//"Approved"=>1,
                        "مرفوض" => 2,//"Rejected"=>2,
                    ),
                ));
                ?>
            </div>
            <br>
        </form>
    </div>
    <br/>
    <br/>
    <div style="background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:20px;padding-right: 20px;">
        <br>
        <div style="text_align:center">
            <?php
            $org_unit_name = '';
            $org_unit_name = $new->get(0, "sub_name");
            echo $org_unit_name;
            ?>
        </div>
        <br>
        <br>
        <?php
        DataTables::create(array(
            "dataSource" => $new,
            "name" => "example",
            "columns" => array(
                "kpi_symbol" => array(
                    "label" => "رمز",
                ),
                "kpi_name" => array(
                    "label" => "الاسم",
                    //"kpi_name",
                ),
                "numerator_name" => array(
                    "label" => "معادلة احتساب المؤشر",
                    //"formula",
                    "formatValue" => function ($value, $row) {
                        if ($row['value_type'] == 2)
                            return "<div class='text-center'>" . $row['numerator_name'] . " &nbsp;/ &nbsp;" . $row['denominator_name'] . "  </div>";
                    }
                ),
                "target_value" => array(
                    "label" => "المستهدف",
                ),
                "acc_value" => array(
                    "label" => "القراءة",
                    "cssStyle" => "overflow-wrap:beak-word;"
                ),
                "to_target" => array(
                    "label" => "القيمة إلى المستهدف",
                    'formatValue' => function ($val, $row) {
                        $target = $row['target_value'];
                        $actual = $row['acc_value'];
                        $data = "";
                        if ($actual != null && $target != null) {
                            $data = ($target - $actual);
                        }
                        return "<div class='text-center'>$data</div>";
                    }
                ),
                "performance_formula" => array(
                    "label" => "الأداء",
                    // "Performance"  ,
                    //"cssStyle"=>"overflow-wrap:beak-word;",
                    // "prefix"=>"%",
                    "type" => "number",
                    "decimals" => 2,
                    'formatValue' => function ($val, $row) {
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

                        return "<div class='text-center'>$data &nbsp;% </div>";
                    }
                ),
                "base_value" => array(
                    "label" => "التقدم",
                    "type" => "number",
                    "decimals" => 2,
                    'formatValue' => function ($val, $row) {
                        $target = $row['target_value'];
                        $actual = $row['acc_value'];
                        $data = "";
                        if(($actual!=null)&&($target!=null))
			            $data = ($val == $target) ? 0 :(($actual - $val) / ($target - $val)) * 100; 
                    
                            $data = round($data, 2);
                            return "<div class='text-center'>$data &nbsp;%</div>";
                        
                    }
                ),
                "next_reading_date" => array(
                    "label" => "تاريخ القراءة التالية",
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
                    array("width" => 10, "targets" => 0),
                    array("width" => 140, "targets" => 1),
                    array("width" => 140, "targets" => 2),
                    array("width" => 60, "targets" => 3),
                    array("width" => 40, "targets" => 4),
                    array("width" => 60, "targets" => 5),
                    array("width" => 40, "targets" => 6),
                    array("width" => 60, "targets" => 7),
                    array("width" => 50, "targets" => 8),
                ),
                "searching" => true,
                "paging" => true,
                // "sorting"=>array(
                //     "kpi_symbol"=>"asc"
                // )
                "orders" => array(
                    array(0, "asc")
                )
            )
        )); ?>
    </div>
    <script type="text/javascript">
        KoolReport.load.onDone(function () {
            var table = $('#example').DataTable({
                destroy: true,
                "language": {
                    "sProcessing": "جارٍ التحميل...",
                    "sLengthMenu": "اعرض _MENU_ سجلات",
                    "sZeroRecords": "لم يعثر على أية سجلات",
                    "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
                    "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix": "",
                    "sSearch": "ابحث:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "الأول",
                        "sPrevious": "السابق",
                        "sNext": "التالي",
                        "sLast": "الأخير"
                    },
                },
                "buttons": [
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
                            $(win.document.body).find('table').addClass('display').css('direction', 'rtl');
                            $(win.document.body).find('tr:nth-child(odd) td').each(function (index) {
                                $(this).css('background-color', '#D0D0D0');
                            });
                            $(win.document.body).find('h1').css('text-align', 'center');
                            $(win.document.body).find('th').css('background-color', '#20a8d8');
                        }
                    },
                ],
                "columnDefs": [{
                    "searchable": true,
                    "orderable": true,
                    "targets": 0
                },
                /*{
                    'visible': false,
                    'targets': 14 ,
                    className: 'noVis'

                },*/
                ], "order": [[0, 'asc']],
                initComplete: function () {
                    /*this.api().columns([1,2,3,4,5,6]).every( function () {
                        var column = this;
                        var title = $(column.header()).text();
                        var head_item = $(column.header());
                        $(head_item ).html('new header');
                        console.log(title);
                        title=title+'@l'
                       s= get_text(title);
                        // $(column.header()).text('hi');
                        // alert( get_text('org_unit@l'))
                    });*/
                    var s = $('#sector');
                    s.on('change', function () {
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
                    $('#kpi_status').on('change', function () {
                        $('#form1').submit();
                    });
                }
            });
            table.buttons().container().appendTo($('#button1'));
            /*table.on( 'order.dt search.dt', function () {
                table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            }).draw();*/
        });
        function get_text(textbit) {
            document.write("hi");
            /*(textbit);
            var language = "<\?php echo $language ?>";
            return textbit
            if ((typeof this.translation[language][textbit] != "undefined" && this.translation[this.$i18n.locale][textbit] != null)) {
                return this.translation[language][textbit];
            } else {
                var spliteText = (textbit.split("@"));
                if (spliteText.length > 2) {
                    var newTxt = (spliteText[0] + '@' + 'l');
                    var transtext1 = this.translation[this.$i18n.locale][newTxt];
                    if (transtext1) {
                        return transtext1;
                    } else {
                        return "!" + textbit;
                    }
                } else {
                    return "!" + textbit;
                }
            }*/
        }

        function myFunction() {
            var x = document.getElementById("myDIV");
            if (x.style.display === "none") {
                $(x).show('slow');
            } else {
                $(x).hide('slow');
            }
        }
    </script>

    <style>
        .buttons-print {
            background-color: #ffffff;
            boder: none;
            /*color:black;*/
        }
        button.dt-button, div.dt-button, a.dt-button, a.dt-button:focus {
            border: none !important;
            background-color: #ffffff;
            background: none;
        }
        .card-body {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.25rem;
        }
        .select2 {
            width: 100% !important;
            /*border: 1px solid #e8e8e8;*/
            min-height: 40px;
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
</body>
</html>

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
use Modules\ClientApp\Reports\KpiValuesReport;

//  $s=2;
//  $data = 10 ** $s;
// //  $data=$data+'%';

// var_dump($data);

$org_unit = '';
if (isset($this->params['org_unit']) && !empty($this->params['org_unit'])) {
    $org_unit = $this->params['org_unit'];
    // var_dump($org_unit);
}

$kpi_symbol = '';
if (isset($this->params['kpi_symbol']) && !empty($this->params['kpi_symbol'])) {
    $kpi_symbol = $this->params['kpi_symbol'];
}
$kpi_name = '';
if (isset($this->params['kpi_name']) && !empty($this->params['kpi_name'])) {
    $kpi_name = $this->params['kpi_name'];
}
$language = '';
if (isset($this->params['language']) && !empty($this->params['language'])) {
    $language = $this->params['language'];
}
$kpi = '';
if (isset($this->params['kpi']) && !empty($this->params['kpi'])) {
    $kpi = $this->params['kpi'];
}
$mtp = '';
if (isset($this->params['mtp']) && !empty($this->params['mtp'])) {
    $mtp = $this->params['mtp'];
}
$value_type = '';
if (isset($this->params['value_type']) && !empty($this->params['value_type'])) {
    $value_type = $this->params['value_type'];
}

$transtable = $this->dataStore('translation');
$mtptable = $this->dataStore('mtp1');
$mtp_name = $mtptable->where('id', $mtp)->get(0, "name");

?>

<!DOCTYPE html>
<?php if ($language == 'ar')
    $dir = "rtl";
else
    $dir = "ltr";
?>
<html dir="<?php echo $dir; ?>">
<!-- <script>window.location.replace('profile');</script> -->
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
</style>
<body>
<div align=center
     style="background-color:#ffffff;margin-left:30px;margin-right:30px;margin-top:0px;margin-bottom:30px;padding-top:30px;">
    <h4 class="mb-0 pt-2" style="color: #20a8d8;font-size: 20px;font-weight: normal;">بيان
        حالة المؤشرات
        <!-- <\?php echo "<script>get_text('org_unit@l')</script>" ?> -->
    </h4>
    <scan class="col-md-3 form-group" style="float: right">
        <script> document.write(new Date().toDateString()); </script>
    </scan>

    <div id="button1" style="float:right;border: 1px solid #a5aeb7;background-color:#ffffff;font-size:18px;"
         class="col-md-12">

        <button onClick="javascript:history.go(-1)" style="float:left;border:none;background-color: #ffffff;"><i
                    style="padding-top:10px;color:#a9a9a9;font-size:12px;" class="fa fa-arrow-left "></i></button>
        <button onClick="javascript:printDiv('details','example1')"
                style="float:left;border:none;background-color: #ffffff;"><i
                    style="padding-top:10px;color:#a9a9a9;font-size:12px" class="fa fa-print "></i></button>
    </div>
    <br/>
    <?php $new = $this->dataStore('user_details')->filter(function ($row) {
        return $row["value"] != null;
    });

    $numerator = $this->dataStore('user_details')->get(0, "numerator_name");
    // $numerator="hi";
    // var_dump($numerator);
    $denominator = $this->dataStore('user_details')->get(0, "denominator_name"); ?>

    <?php $style = "";
    if (empty($_POST)) {
        $style = 'display:none !important;';
    } else {
        $style = 'display:block !important;';
    } ?>

    <div class="col-md-12"
         style="background-color:#ffffff;border: 1px solid #a5aeb7;padding-top:10px;margin-bottom:20px;border-radius: 0.25rem;<?php echo $style; ?>;">
        <br>
    </div>
    <br/>
    <br/>
    <div style="background-color:#ffffff;border: 1px solid #a5aeb7; padding-top:20px;padding-bottom:20px;padding-left:20px;padding-right: 20px;">
        <br>
        <?php if ($language == 'ar') {
            $dir = "rtl";
            $lin = "right";
        } else {
            $dir = "ltr";
            $lin = "left";
        }
        ?>
        <div id="details" dir="<?php echo $language == 'ar' ? "rtl" : "ltr"; ?>">
            <div class="row">
                <div class="col-md-2 form-group" style="text-align:right; color:#73818f;float: <?php echo $lin; ?>;">
                    <strong>
                        <?php $textbit = 'org_unit';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
                        echo $translation; ?>
                    </strong> <!-- sector -->
                </div>
                <div class="col-md-4 form-group" style="text-align:right; float: <?php echo $lin; ?>;">
                    <strong>
                        <?php echo $this->params['org_unit'] ?>
                    </strong>
                </div>
                <div class="col-md-2 form-group" style="text-align:right;color:#73818f; float: <?php echo $lin; ?>;">
                    <strong>     <!-- <\?php echo get_text('mtp',$language,$kpi,$mtp);?> -->
                        <?php $textbit = 'mtp';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
                        echo $translation; ?>
                    </strong>
                </div>
                <div class="col-md-4 form-group" style="text-align:right; float: <?php echo $lin; ?>;">
                    <strong>
                        <?php echo $mtp_name//$this->params['mtp']?>
                    </strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 form-group" style="text-align:right;color:#73818f; float: <?php echo $lin; ?>;">
                    <!-- <\?php echo get_text('symbol',$language,$kpi,$mtp);?> -->
                    <strong>
                        <?php $textbit = 'symbol';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
                        echo $translation; ?>
                    </strong>
                </div>
                <div class="col-md-4 form-group" style="text-align:right; float: <?php echo $lin; ?>;">
                    <strong>
                        <?php echo $this->params['kpi_symbol'] ?>
                    </strong>
                </div>
                <div class="col-md-2 form-group" style="text-align:right; color:#73818f;float: <?php echo $lin; ?>;">
                    <strong>
                        <?php $textbit = 'kpi_name';
                        $translation = ($language == 'en' ? $transtable->where('key_name', $textbit)->get(0, "value_en") : $transtable->where('key_name', $textbit)->get(0, "value_ar"));
                        echo $translation ?>
                    </strong>
                </div>
                <div class="col-md-4 form-group" style="text-align:right; float: <?php echo $lin; ?>;">
                    <strong>
                        <?php echo $this->params['kpi_name'] ?>
                    </strong>
                </div>
            </div>
        </div>
        <br>
        <br>
        <?php
        DataTables::create(array(
            "dataSource" => $new,
            "name" => "example1",
            "columns" => array(
                "scheduled_date" => array(
                    "label" => ($language == 'en' ? $transtable->where('key_name', 'target_date')->get(0, "value_en") : $transtable->where('key_name', 'target_date')->get(0, "value_ar")),
                    "formatValue" => function ($value, $row) {
                        return "<div>$value</div>";
                    }
                ),
                "value" => array(
                    "label" => ($language == 'en' ? $transtable->where('key_name', 'value')->get(0, "value_en") : $transtable->where('key_name', 'value')->get(0, "value_ar")),
                    "formatValue" => function ($value, $row) {
                        $language = $this->params['language'];
                        $lin = $language == 'en' ? 'left' : 'right';
                        $dec = $row['rounding_decimals'];
                        if ($row['value_type'] == 2) {
                            $value = $value * 100;
                        }
                        $value = round($value, $dec);
                        $value = number_format((float)($value));
                        return "<div dir=\"ltr\" style=\"text-align:$lin\">$value %</div>";
                    }
                ),
                "value_numerator" => array(
                    "label" => $numerator,
                    "formatValue" => function ($value, $row) {
                        return "<div>$value</div>";
                    }
                ),
                "value_denominator" => array(
                    "label" => $denominator,
                    "formatValue" => function ($value, $row) {
                        return "<div>$value</div>";
                    }
                ),
                "value_type" => array(
                    "label" => ($language == 'en' ? $transtable->where('key_name', 'value_type')->get(0, "value_en") : $transtable->where('key_name', 'value_type')->get(0, "value_ar")),
                    "formatValue" => function ($value, $row) {
                        $transtable = $this->dataStore('translation');

                        $language = $this->params['language'];

                        if ($value == 1) {
                            $term = ($language == 'en' ? $transtable->where('key_name', 'number')->get(0, "value_en") : $transtable->where('key_name', 'number')->get(0, "value_ar"));
                            return "<div>$term</div>";
                        }
                        if ($value == 2) {
                            $term = ($language == 'en' ? $transtable->where('key_name', 'percentage')->get(0, "value_en") : $transtable->where('key_name', 'percentage')->get(0, "value_ar"));
                            return "<div>$term</div>";
                        }
                        if ($value == 3) {
                            $term = ($language == 'en' ? $transtable->where('key_name', 'ratio')->get(0, "value_en") : $transtable->where('key_name', 'ratio')->get(0, "value_ar"));
                            return "<div>$term</div>";
                        }
                        if ($value == 4) {
                            $term = ($language == 'en' ? $transtable->where('key_name', 'rate')->get(0, "value_en") : $transtable->where('key_name', 'rate')->get(0, "value_ar"));
                            return "<div>$term</div>";
                        }
                    }
                ),
                "value_date" => array(
                    "label" => ($language == 'en' ? $transtable->where('key_name', 'actual_date')->get(0, "value_en") : $transtable->where('key_name', 'actual_date')->get(0, "value_ar")),
                    "formatValue" => function ($value, $row) {
                        return "<div>$value</div>";
                    }
                ),
                "notes" => array(
                    "label" => ($language == 'en' ? $transtable->where('key_name', 'notes')->get(0, "value_en") : $transtable->where('key_name', 'notes')->get(0, "value_ar")),
                    "formatValue" => function ($value, $row) {
                        return "<div>$value</div>";
                    }
                ),
            ),
            "cssClass" => array(
                "table" => "table table-striped table-bordered color  ",
                "th" => "cssHeader insideBorder ",
                "tr" => "cssItem color",
                "td" => "insideBorder"
            ),
            "options" => array(
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
            var details = $('#details').innerHTML;
            var table1 = $('#example1').DataTable({
                "pageLength": 25,
                destroy: true,
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
                },
                "columnDefs": [{
                    "searchable": true,
                    "orderable": true,
                    "targets": 0
                },
                ], "order": [[0, 'asc']],
            });
            var vt =<?php echo $value_type?>;
            if (vt == 1)
                table1.columns([2, 3]).visible(false);
            table1.buttons().container().appendTo($('#button1'));
        });

        function printDiv(divID, table) {
            //Get the HTML of div
            var divElements = document.getElementById(divID).innerHTML;
            var table = $('#example1').DataTable()
            var heading = "بيان  حالة المؤشرات"
            var tableTag = "<table id=\"example1\" class=\"table table-striped table-bordered color no-footer dataTable\" role=\"grid\" aria-describedby=\"example_info\" style=\"width: 1234px;\">";
            var thead = table.table().header().outerHTML;
            var rows = table.rows({search: 'applied'}).nodes();
            var rowStr = "";
            for (var i = 0; i < rows.length; i++)
                rowStr += rows[i].outerHTML;
            var divElements = document.getElementById(divID).innerHTML;

            var datatest = tableTag + thead + rowStr + divElements//$('#example').prop('outerHTML')//$('#example').wrapAll('<div>').parent().html();
            data1 = "<html dir=\"rtl\" lang=\"ar\"><head><meta charset=\"utf-8\"> <title>" + heading + "</title></head><body><div style=\"text-align:center\"><h1>" + heading + "</h1></div>" + datatest + "</table></body></html>";
            var oldPage = document.body.innerHTML;

            document.body.innerHTML = data1;
            window.print();
            document.body.innerHTML = oldPage;

        }

    </script>
    <style>
        @media print {
            .header-print {
                display: table-header-group;
            }
        }

        .buttons-print {
            background-color: #ffffff;
            boder: none;
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

        button.dt-button {
            font-size: 0.68em;
        }

        .select2 {
            width: 100% !important;
            min-height: 40px;
        }

    </style>
</body>
</html>

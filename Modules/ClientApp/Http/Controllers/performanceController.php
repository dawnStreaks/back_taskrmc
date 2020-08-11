<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class performanceController extends Controller
{
    //
    public function index()
    {

        $perfprog = DB::select(
            "call p_unit_perf_prog(114,4,'Q1',1);"
        );
        foreach ($perfprog as $row) {
            $kpishowdata[] = [
                'KPIdata' => 'Symbol: ' . $row->kpi_symbol,
                'importance' => $row->weight,
                'importancetxt' => 'Importance: ' . $row->weight,
                'performance' => $row->kpi_perf * 100,
                'performancetxt' => 'Performance: ' . (number_format($row->kpi_perf, 2) * 100) . '%',
                'progress' => 'Progress: ' . (number_format($row->kpi_prog, 2) * 100) . '%',
                'KPIname' => $row->kpi_name,
                'adjustedweight' => $row->adjusted_weight,
                'weightedperformance' => $row->adjusted_weight * $row->kpi_perf,


            ];
        }


        header('Content-type: application/json');
        //   echo json_encode($kpishowdata);


        if ($kpishowdata) {
            return response()->json([
                "code" => 200,
                "kpishowdata" => $kpishowdata
            ]);
        }

        return response()->json(["code" => 400]);
    }

    public function loadTenants()
    {
        $tenants = \DB::select(\DB::raw("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3"));
        if ($tenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $tenants
            ]);
        }
    }


    public function sectionkpireport($value)
    {
        $debug_mode = true;
        $debug_show_icon = 0;
        $debug_show_icon_best = 0;
        $debug_show_icon_least = 0;
        $kpi_perf = 0;
        $debug_progmode = 0;
        $debug_show_icon_prog = 0;
        $debug_tooltip_prog = 0;
        $valuearray = explode(",", $value);
        $supervisionback='';
        $ministryback='';

        $section = $valuearray[0];
        $mtp = $valuearray[1];
        $asofperiod = $valuearray[2];
        $yearno = $valuearray[3];
        if($valuearray[4]) {
           $supervisionback = $valuearray[4];
        }
        if($valuearray[5]) {
            $ministryback = $valuearray[5];
        }

        $deptid = \DB::select(\DB::raw("select parent_id from subtenant where id=$section"));
        $deptval = $deptid[0]->parent_id;
        $supervision=$deptval;

        $sectorpid = \DB::select(\DB::raw("select parent_id from subtenant where id=$deptval"));
        $sectorpval = $sectorpid[0]->parent_id;
        $sectorid = \DB::select(\DB::raw("select parent_id from subtenant where id=$sectorpval"));
        $sectorval = $sectorid[0]->parent_id;


        if ($sectorval == 2) {
            $sectorval = $sectorpval;
        }
        $perfprog = DB::select(
            "call p_unit_perf_prog($section,$mtp,'$asofperiod',$yearno);"
        );
//       var_dump($perfprog);
//       die();
        $acheivedcount = 0;
        $uptodatecount = 0;
        $unitperformance = 0;
        $unitprogress = 0;
        $nonuptodatecount = 0;
        $performingkpicount = 0;
        $nonperformingkpicount = 0;

        foreach ($perfprog as $row) {
            if ($row->kpi_perf * 100 >= 50) {
                $acheivedcount = $acheivedcount + 1;
                $performingkpicount = $performingkpicount + 1;
            }
            if ($row->kpi_perf * 100 < 50) {

                $nonperformingkpicount = $nonperformingkpicount + 1;
            }

            $weighedperf = $row->adjusted_weight * $row->kpi_perf;
            $weighedprog = $row->adjusted_weight * $row->kpi_prog;
            $unitperformance = $unitperformance + $weighedperf;
            $unitprogress = $unitprogress + $weighedprog;
            if ($row->kpi_up_to_date == 1) {
                $uptodatecount = $uptodatecount + 1;

            }
            if ($row->kpi_up_to_date == 0) {
                $nonuptodatecount = $nonuptodatecount + 1;

            }


            if ($debug_mode == true) {
                if ($row->kpi_perf * 100 > 100) {
                    $kpi_perf = 100;
                    $debug_show_icon = 1;
                    $debug_show_icon_perf_tool = '<i style="color:#ffffff;margin-left:10px;margin-right:10px;" class="fas fa-exclamation-triangle fa-1x"></i>';

                } else {
                    $kpi_perf = $row->kpi_perf * 100;
                    $debug_show_icon = 0;
                    $debug_show_icon_perf_tool = '';

                }

            }
            if ($debug_progmode == false) {
                if ($row->kpi_prog * 100 < 0) {
                    $kpi_prog = 0;
                    $debug_show_icon_prog = 1;
                } else {
                    $kpi_prog = $row->kpi_prog * 100;
                    $kpi_prog = (number_format($kpi_prog, 2));
                    $debug_show_icon_prog = 0;
                }

            }

            $uptodateicon = '';
            $debugicon = '';

            if ($row->kpi_up_to_date == 0) {
                $uptodateicon = '<i class="fas fa-exclamation-circle fa-2x"></i>';
            }
            if ($debug_show_icon == 1 || $debug_show_icon_prog == 1) {
                $debugicon = '<i style="color:#ffffff" class="fas fa-exclamation-triangle fa-2x"></i>';
            }

            $gaugelink = "gaugechart/" . $row->kpi_id . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . ",fromsector," . $sectorval;
            if($supervisionback!='' || $supervisionback!='') {
                if ($supervisionback == 'supervision') {
                    $superbacklink = ',' . $supervision;
                    $gaugelink = $gaugelink . $superbacklink;
                }
                if ($ministryback == 'ministry') {
                    $ministrybacklink = ',' . 2;
                    $gaugelink = $gaugelink . $ministrybacklink;
                }
            }
           else{
               $gaugelink = "gaugechart/" . $row->kpi_id . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . ",fromsector," . $sectorval;

           }
            $kpishowdata[] = [
                'KPIdata' => $row->kpi_symbol,

                'importance' => $row->weight,
                'importancetxt' => 'Importance: ' . $row->weight,
                'performance' => $kpi_perf,
                'performancetxt' => (number_format($kpi_perf)) . '%',
                'progress' => $kpi_prog . '%',
                'KPIname' => $row->kpi_name,
                'adjustedweight' => (number_format($row->adjusted_weight * 100, 2)) . '%',
                'weightedperformance' => (number_format(($row->adjusted_weight * $row->kpi_perf) * 100, 2)) . '%',
                'kpi_up_to_date' => $row->kpi_up_to_date,
                'debug_show_icon' => $debug_show_icon,
                "link" => $gaugelink,
                "button" => '<div style="color:#ffffff" >' . $uptodateicon . $debugicon


            ];
        }
        $unitperformance = (number_format($unitperformance * 100, 2));
        $unitprogress = (number_format($unitprogress * 100, 2));
        if ($debug_progmode == 0) {
            if ($unitprogress < 0) {
                $unitprogress = 0;
                $debug_show_icon_prog = 1;
            } else {
                $unitprogress = $unitprogress;
                $debug_show_icon_prog = 0;
            }
        }


        header('Content-type: application/json');
        //   echo json_encode($kpishowdata);


        if ($kpishowdata) {
            return response()->json([
                "code" => 200,
                "kpishowdata" => $kpishowdata,
                "totalkpi" => count($perfprog),
                "acheivedcount" => $acheivedcount,
                "uptodatecount" => $uptodatecount,
                "unitperformance" => $unitperformance,
                "unitprogress" => $unitprogress,
                "nonuptodatecount" => $nonuptodatecount,
                "performingkpicount" => $performingkpicount,
                "nonperformingkpicount" => $nonperformingkpicount,
                "debug_show_icon_prog" => $debug_show_icon_prog


            ]);
        }

        return response()->json(["code" => 400]);
    }

    public function departmentreport($value)
    {

        $debug_mode = true;
        $debug_show_icon = 0;
        $debug_show_icon_best = 0;
        $debug_show_icon_least = 0;
        $kpi_color = 0;
        $superlink = '';
        $superlinkar = '';
        $debug_progmode = 0;
        $debug_show_icon_prog = 0;
        $debug_show_icon_progstrategic = 0;
        $debug_show_icon_progic = '';
        $debug_show_icon_perf_tool = '';

        $valuearray = explode(",", $value);

        $dept = $valuearray[0];
        $mtp = $valuearray[1];
        $asofperiod = $valuearray[2];
        $yearno = $valuearray[3];
        $type = $valuearray[4];
        $kpistrategic_sector = [];

        $dptperfprog = DB::select(
            " call p_unit_perf_prog_recursive($dept, $mtp,'$asofperiod',$yearno);"
        );
        $kpistrategic_perf = 0;
        $kpistrategic_perf = 0;
        $kpistrategic_prog = 0;
        $kpistrategic_prog = 0;
        $kpistrategic_kpi = 0;
        if ($type == 0 || $type == 1) {
            $dptkpistrategic = DB::select("call p_unit_perf_prog_a_scope(
				$dept, $mtp,'$asofperiod',$yearno,'O');"
            );
            $kpistrategic_perfarr = [];
            $kpistrategic_progarr = [];
            $kpistrategic_kpiarr = [];
            $kpistrategicvalues = [];
            if ($dptkpistrategic) {
                if ($type == 0) {
                    $kpistrategic_perf = $dptkpistrategic[0]->kpi_perf;
                    $kpistrategic_perf = (number_format($kpistrategic_perf * 100, 2));
                    $kpistrategic_prog = $dptkpistrategic[0]->kpi_prog;
                    if ($debug_mode == true) {
                        if ($kpistrategic_prog < 0) {
                            $kpistrategic_prog = 0;
                            $debug_show_icon_progstrategic = 1;
                        } else {
                            $kpistrategic_prog = (number_format($dptkpistrategic[0]->kpi_prog * 100, 2));
                            $debug_show_icon_progstrategic = 0;
                        }

                    }
                    //$kpistrategic_prog =$kpistrategic_prog ;
                    $kpistrategic_kpi = $dptkpistrategic[0]->kpi_name;
                }
                if ($type == 1) {
                    foreach ($dptkpistrategic as $dptkpistrategicval) {
                        $kpistrategic_perf = $dptkpistrategicval->kpi_perf;
                        $kpistrategic_perfarr = (number_format($kpistrategic_perf * 100, 2));
                        $kpistrategic_prog = $dptkpistrategicval->kpi_prog;
                        $kpistrategic_progarr = (number_format($kpistrategic_prog * 100, 2));
                        $kpistrategic_kpiarr = $dptkpistrategicval->kpi_name;

                        if ($debug_progmode == 0) {
                            if ($kpistrategic_progarr < 0) {
                                $kpistrategic_progarr = 0;
                                $debug_show_icon_prog = 1;
                            } else {
                                $kpistrategic_progarr = $kpistrategic_progarr;
                                $debug_show_icon_prog = 0;
                            }
                        }
                        $kpistrategicvalues[] = [
                            'kpigoalname' => $kpistrategic_kpiarr,
                            'kpigoalperf' => $kpistrategic_perfarr,
                            'kpigoalprog' => $kpistrategic_progarr,
                            'kpigoalkpiid' => $dptkpistrategicval->kpi_id

                        ];

                    }

                }
            }

        }
        if ($type == 0) {
            $sectorlink = '';
            $sectorlinkar = '';
            foreach ($dptperfprog as $row) {
//                if ($row->calc_level = 1 && isset($row->perf_sum_w)) {
//                    $kpiperfarray[] = $row->perf_sum_w;
//
//                }
                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);
                if (isset($dataarray[0])) {
                    $supervisionarray[] = $dataarray[0];


                }

            }

            $supervision = '';
            $kpi_count = 0;
            $section = '';
            $supervisionname = '';
            $sectionname = '';
            $kpitotcount = $dptperfprog[0]->child_count_eff;
            $performingkpicount = $dptperfprog[0]->performing_count;
            $nonperformingkpicount = $kpitotcount - $performingkpicount;

            foreach ($dptperfprog as $row) {
//echo $row->perf_sum_w;
                if ($row->calc_level = 1 && isset($row->perf_sum_w)) {
                    $kpiperfarray[] = $row->perf_sum_w;

                }
//            else{
//                $kpiperfarray[] =0;
//            }


                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);

                $dept = preg_replace('/[^A-Za-z0-9]/', '', $dataarray[0]);
                $deptnameval = \DB::select(\DB::raw("select name from subtenant where id=$dept"));
                $deptname = $deptnameval[0]->name;
                if (isset($dataarray[1])) {
                    $supervision = $dataarray[1];
                    $supervisionnameval = \DB::select(\DB::raw("select name from subtenant where id=$supervision"));
                    $supervisionname = $supervisionnameval[0]->name;
                }
                if (isset($dataarray[2])) {
                    $section = $dataarray[2];
                    $sectionnameval = \DB::select(\DB::raw("select name from subtenant where id=$section"));
                    $sectionname = $sectionnameval[0]->name;

                }


                if ($row->kpi_importance_sum == null) {
                    $row->kpi_importance_sum = 0;
                }
                if ($row->kpi_count == null) {
                    $row->kpi_count = 0;
                }
                $subtenant_type_id = \DB::select(\DB::raw("select subtenant_type_id from subtenant where id=$row->sub_id"));
                $subtenant_type_idval = $subtenant_type_id[0]->subtenant_type_id;
//            if ($subtenant_type_idval == 9) {
//                $section = $supervision;
//                $sectionname = $supervisionname;
//            }
                $kpi_perfsum = (number_format($row->perf_sum_w * 100, 2));
                if ($debug_mode == true) {
                    if ($kpi_perfsum > 100) {
                        $kpi_perfsum = 100;
                        $debug_show_icon = 1;
                    } else {
                        $debug_show_icon = 0;
                    }

                }
                if ($kpi_perfsum >= 40 && $kpi_perfsum <= 60) {
                    $kpi_color = 1;

                } else {
                    $kpi_color = 0;
                }
                $kpid1[] = [


                    'supervision' => $supervision,
                    "dept" => $dept,
                    'section' => $section,
                ];

                if ($supervision == '') {
                    $supervision = $dept;
                    $deptidid = \DB::select(\DB::raw("select parent_id from subtenant where id=$dept"));
                    $deptidval = $deptidid[0]->parent_id;
                    $sectordid = \DB::select(\DB::raw("select parent_id from subtenant where id=$deptidval"));
                    $sectoridval = $sectordid[0]->parent_id;
//                $sectorlink='';
//                $sectorlinkar='';
//                if($supervision) {
//
//                    //$sectorlink = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",supervision=" . $sectoridval . '>Show Section Report</a></div>';
//                   // $sectorlinkar = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",supervision=" . $sectoridval . '>عرض أداء القسم</a></div>';
//
//                   // $superlinkar = '<div style=float:left" style="color:#ffffff" ><a style="margin-left: 500px;color:#ffffff;font-size:16px;" href=\'. "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept" .\'> عرض أداء المراقبة </a></div>\'<br><div style="float:left" style="color:#ffffff" ><a style="margin-left: 500px;color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept" . '> عرض أداء المراقبة </a></div>';
//                    $superlink='';
//
//
//                }
//            }
//            else {
//                if($this->arraycount($supervisionarray, $supervision)==1){
//                   // $superlink ='<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href='. "/departmentperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept".'">Show Supervision Report</a><br><a style="color:#ffffff;font-size:16px;" href='. "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sectoridval.'>Show Section Report</a></div>';
//                 //   $superlinkar ='<div style="float:left" style="color:#ffffff" ><a style="margin-left: 500px;color:#ffffff;font-size:16px;" href=\'. "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept" .\'> عرض أداء المراقبة </a></div>\'<br><div style="float:left" style="color:#ffffff" ><a style="margin-left: 500px;color:#ffffff;font-size:16px;" href='. "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept" .'> عرض أداء المراقبة </a></div>';
//                   // $sectorlink = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . '>Show Section Report</a></div>';
//
//
//
//                }
                    if ($dataarray[0]) {
                        if ($this->arraycount($supervisionarray, $dataarray[0]) == 1) {
                            $sectorlink = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sectoridval . '>Show Section Report</a></div>';
                            $sectorlinkar = '<div style="color:#ffffff;margin-top: 20px;margin-left: -120px;" ><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sectoridval . '>عرض أداء القسم</a></div>';


                        } else {
                            $superlink = '';
                            $superlinkar = '';
                        }

                    }
                }

                if ($debug_progmode == 0) {
                    if ($row->prog_sum_w < 0) {
                        $kpi_progsum = 0;
                        $debug_show_icon_prog_tool = 1;
                        $debug_show_icon_progic = '<i style="margin-left:10px;margin-right:10px;color:#ffffff" class="fas fa-exclamation-triangle fa-1x"></i>';
                    } else {
                        $kpi_progsum = (number_format($row->prog_sum_w * 100, 2));

                        $debug_show_icon_prog_tool = 0;
                        $debug_show_icon_progic = '';
                    }
                }


                $kpishowdata[] = [
                    'Category' => 'deptreport',
                    'deptname' => $deptname,
                    'supervision' => $supervision,
                    'section' => $section,
                    'kpi_importancesum' => $row->kpi_importance_sum,
                    'kpi_perfsum' => $kpi_perfsum,
                    'kpi_progsum' => $kpi_progsum,
                    'calc_level' => $row->calc_level,
                    'supervisionname' => $supervisionname,
                    'sectionname' => $sectionname,
                    'kpicount' => $row->kpi_count,
                    'unitcount' => $row->unit_count,
                    "dept" => $dept,
                    "mtp" => $mtp,
                    "asofperiod" => $asofperiod,
                    "yearno" => $yearno,
                    "type" => $type,
                    "debug_show_icon" => $debug_show_icon,
                    "link" => "/sectionperformance" . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept,
                    "supervisionlink" => "/departmentperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept",
                    "kpi_color" => $kpi_color,
                    "button" => $superlink,
                    "buttonar" => $superlinkar,
                    "sectorlink" => $sectorlink,
                    "sectorlinkar" => $sectorlinkar,
                    "debug_show_icon_prog_tool" => $debug_show_icon_prog_tool,
                    "debug_show_icon_progic" => $debug_show_icon_progic,
                    "debug_show_icon_perf_tool" => $debug_show_icon_perf_tool
                    //  "button"=>$button1


                ];


                $supervision = '';
                $section = '';
                $supervisionname = '';
                $sectionname = '';
                $dataarray = [];
            }


            $unitperformance = $dptperfprog[0]->perf_sum_w;
            $unitprogress = $dptperfprog[0]->prog_sum_w;

            $unitperformance = (number_format($unitperformance * 100, 2));
            $unitprogress = (number_format($unitprogress * 100, 2));
            $kpibestperforming = max($kpiperfarray);
            $kpibestperforming = (number_format($kpibestperforming * 100, 2));
            if ($debug_mode == true) {
                if ($kpibestperforming > 100) {
                    $kpibestperforming = 100;
                    $debug_show_icon_best = 1;
                } else {
                    $debug_show_icon_best = 0;
                }

            }
            $kpileastperforming = min($kpiperfarray);
            $kpileastperforming = (number_format($kpileastperforming * 100, 2));
            if ($debug_mode == true) {
                if ($kpileastperforming > 100) {
                    $kpileastperforming = 100;
                    $debug_show_icon_least = 1;
                } else {
                    $debug_show_icon_least = 0;
                }

            }
        }

        if ($type == 1) {
            $sector = '';
            $sectorname = '';
            $supervision = '';
            $kpi_count = 0;
            $section = '';
            $supervisionname = '';
            $sectionname = '';
            $deptname = '';
            $dept = '';
            $kpitotcount = $dptperfprog[0]->child_count_eff;
            $performingkpicount = $dptperfprog[0]->performing_count;
            $nonperformingkpicount = $kpitotcount - $performingkpicount;
            $i = 0;
            $kpistrategic_sector = [];
            foreach ($dptperfprog as $row) {
//                if ($row->calc_level = 1 && isset($row->perf_sum_w)) {
//                    $kpiperfarray[] = $row->perf_sum_w;
//
//                }
                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);
                if (isset($dataarray[2])) {
                    $supervisionarray[] = $dataarray[2];


                }

            }

            foreach ($dptperfprog as $row) {

                if ($row->calc_level = 1 && isset($row->perf_sum_w)) {
                    $kpiperfarray[] = $row->perf_sum_w;

                }
//              var_dump($dptperfprog);
//                die();

                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);

                $sector = preg_replace('/[^A-Za-z0-9]/', '', $dataarray[0]);
                $sectornameval = \DB::select(\DB::raw("select name from subtenant where id=$sector"));
                $sectorname = $sectornameval[0]->name;
                if (isset($dataarray[1])) {
                    $dept = $dataarray[1];
                    $deptnameval = \DB::select(\DB::raw("select name from subtenant where id=$dept"));
                    $deptname = $deptnameval[0]->name;
                    $depts[] = $dept;

                }

                if (isset($dataarray[2])) {
                    $supervision = $dataarray[2];
                    $supervisionnameval = \DB::select(\DB::raw("select name from subtenant where id=$supervision"));
                    $supervisionname = $supervisionnameval[0]->name;

                }
                if (isset($dataarray[3])) {
                    $section = $dataarray[3];
                    $sectionnameval = \DB::select(\DB::raw("select name from subtenant where id=$section"));
                    $sectionname = $sectionnameval[0]->name;

                }


                if ($row->kpi_importance_sum == null) {
                    $row->kpi_importance_sum = 0;
                }
                if ($row->kpi_count == null) {
                    $row->kpi_count = 0;
                }
                $subtenant_type_id = \DB::select(\DB::raw("select subtenant_type_id from subtenant where id=$row->sub_id"));
                $subtenant_type_idval = $subtenant_type_id[0]->subtenant_type_id;
//                if ($subtenant_type_idval == 9) {
//                    $section = $supervision;
//                    $sectionname = $supervisionname;
//                }
                $kpi_perfsum = (number_format($row->perf_sum_w * 100, 2));
                if ($debug_mode == true) {
                    if ($kpi_perfsum > 100) {
                        $kpi_perfsum = 100;
                        $debug_show_icon = 1;
                        $debug_show_icon_perf_tool = '<i style="color:#ffffff;margin-left:10px;margin-right:10px;" class="fas fa-exclamation-triangle fa-1x"></i>';

                    } else {
                        $debug_show_icon = 0;
                        $debug_show_icon_perf_tool = '';
                    }

                }
                if ($kpi_perfsum >= 40 && $kpi_perfsum <= 60) {
                    $kpi_color = 1;

                } else {
                    $kpi_color = 0;
                }
                $kpid1[] = [

                    'sector' => $sector,

                    'supervision' => $supervision,
                    "dept" => $dept,
                    'section' => $section,
                ];
                if ($supervision) {
                    if ($this->arraycount($supervisionarray, $supervision) == 1) {
                        $superlink = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . '>Show Supervision Report</a><br><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . '>Show Section Report</a></div>';
                        $superlinkar = '<div style="color:#ffffff;margin-top: 20px;margin-left: -120px;" ><a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . '>عرض أداء المراقبة</a><br><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . '>عرض أداء القسم</a></div>';


                    } else {
                        $superlink = '';
                        $superlinkar = '';
                    }

                }
                $button1 = '<i style="color:#ffffff" class="fas fa-exclamation-triangle fa-2x"></i>';;

                //$superlink='';
                // $superlink="/sectionperformance" . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept.',sector='.$sector;
                if ($debug_progmode == 0) {
                    if ($row->prog_sum_w < 0) {
                        $kpi_progsum = 0;
                        $debug_show_icon_prog_tool = 1;
                        $debug_show_icon_progic = '<i style="color:#ffffff;margin-left:10px;margin-right:10px;" class="fas fa-exclamation-triangle fa-1x"></i>';

                    } else {
                        $kpi_progsum = (number_format($row->prog_sum_w * 100, 2));

                        $debug_show_icon_prog_tool = 0;
                        $debug_show_icon_progic = '';

                    }
                }
                $kpishowdata[] = [
                    'Category' => 'deptreport',
                    'sectorname' => $sectorname,
                    'sector' => $sector,
                    'deptname' => $deptname,
                    'supervision' => $supervision,
                    'section' => $section,
                    'kpi_importancesum' => $row->kpi_importance_sum,
                    'kpi_perfsum' => $kpi_perfsum,
                    'kpi_progsum' => $kpi_progsum,
                    'calc_level' => $row->calc_level,
                    'supervisionname' => $supervisionname,
                    'sectionname' => $sectionname,
                    'kpicount' => $row->kpi_count,
                    'unitcount' => $row->unit_count,
                    "dept" => $dept,
                    "mtp" => $mtp,
                    "asofperiod" => $asofperiod,
                    "yearno" => $yearno,
                    "type" => $type,
                    "debug_show_icon" => $debug_show_icon,
                    "link" => "/sectionperformance" . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ',sector=' . $sector,
                    "supervisionlink" => "/departmentperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector,
                    'kpi_color' => $kpi_color,
                    "button" => $superlink,
                    "buttonar" => $superlinkar,
                    "debug_show_icon_prog_tool" => $debug_show_icon_prog_tool,
                    "debug_show_icon_progic" => $debug_show_icon_progic,
                    "debug_show_icon_perf_tool" => $debug_show_icon_perf_tool


                ];
                $supervisionarray[] = $supervision;

                $sectorname = '';
                $sector = '';
                $supervision = '';
                $section = '';
                $supervisionname = '';
                $sectionname = '';
                $dataarray = [];

                $i++;
            }
            array_unique($depts);


            $duplicate_keys = array();
            $tmp = array();

            foreach ($depts as $key => $val) {
                // convert objects to arrays, in_array() does not support objects
                if (is_object($val))
                    $val = (array)$val;

                if (!in_array($val, $tmp))
                    $tmp[] = $val;
                else
                    $duplicate_keys[] = $key;
            }

            foreach ($duplicate_keys as $key)
                unset($depts[$key]);
            $i = 0;
            foreach ($depts as $deptvalue) {
                $dptkpistrategic_sector = DB::select("call p_unit_perf_prog_a_scope(
				$deptvalue, $mtp,'$asofperiod',$yearno,'O');"
                );

                if (count($dptkpistrategic_sector) != 0) {
//                       var_dump($dptkpistrategic_sector);
//                       $kpistrategic_sector_perf = $dptkpistrategic_sector->kpi_perf;
//                       echo $kpistrategic_sector_perf;
//                       $kpistrategic_sector_perf = (number_format($kpistrategic_perf * 100, 2));
//                       $kpistrategic_sector_prog = $dptkpistrategic_sector[0]->kpi_prog;
//                        $kpistrategic_sector_prog = (number_format($kpistrategic_prog * 100, 2));
//                       $kpistrategic_sector_kpi = $dptkpistrategic_sector[0]->kpi_name;
//
//                        $kpistrategic_sector[$i]->perf=$kpistrategic_sector_perf;
//                       $kpistrategic_sector[$i]->prog=$kpistrategic_sector_prog;
//                       $kpistrategic_sector[$i]->kpi=$kpistrategic_sector_kpi;
//                        $kpistrategic_sector[$i]->dept=$dept;
                    $kpistrategic_sector[] = $dptkpistrategic_sector;


                }

                $i++;
            }

            $unitperformance = $dptperfprog[0]->perf_sum_w;
            $unitprogress = $dptperfprog[0]->prog_sum_w;

            $unitperformance = (number_format($unitperformance * 100, 2));
            $unitprogress = (number_format($unitprogress * 100, 2));
            $kpibestperforming = max($kpiperfarray);
            $kpibestperforming = (number_format($kpibestperforming * 100, 2));
            if ($debug_mode == true) {
                if ($kpibestperforming > 100) {
                    $kpibestperforming = 100;
                    $debug_show_icon_best = 1;
                } else {
                    $debug_show_icon_best = 0;
                }

            }
            $kpileastperforming = min($kpiperfarray);
            $kpileastperforming = (number_format($kpileastperforming * 100, 2));
            if ($debug_mode == true) {
                if ($kpileastperforming > 100) {
                    $kpileastperforming = 100;
                    $debug_show_icon_least = 1;
                } else {
                    $debug_show_icon_least = 0;
                }

            }
        }

        if ($type == 2) {
            $supervision = '';
            $kpi_count = 0;
            $section = '';
            $supervisionname = '';
            $sectionname = '';
            $sectorlink = '';
            $sectorlinkar = '';
            $kpitotcount = $dptperfprog[0]->child_count_eff;
            $performingkpicount = $dptperfprog[0]->performing_count;
            $nonperformingkpicount = $kpitotcount - $performingkpicount;

            foreach ($dptperfprog as $row) {
                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);
                if (isset($dataarray[0])) {
                    $supervisionarray[] = $dataarray[0];


                }

            }
            foreach ($dptperfprog as $row) {

                if ($row->calc_level = 1 && isset($row->perf_sum_w)) {
                    $kpiperfarray[] = $row->perf_sum_w;

                } else {
                    $kpiperfarray[] = 0;
                }


                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);

                $supervision = preg_replace('/[^A-Za-z0-9]/', '', $dataarray[0]);
                $supervisionnameval = \DB::select(\DB::raw("select name from subtenant where id=$supervision"));
                $supervisionname = $supervisionnameval[0]->name;

                if (isset($dataarray[1])) {
                    $section = $dataarray[1];
                    $sectionnameval = \DB::select(\DB::raw("select name from subtenant where id=$section"));
                    $sectionname = $sectionnameval[0]->name;

                }


                if ($row->kpi_importance_sum == null) {
                    $row->kpi_importance_sum = 0;
                }
                if ($row->kpi_count == null) {
                    $row->kpi_count = 0;
                }
                $subtenant_type_id = \DB::select(\DB::raw("select subtenant_type_id from subtenant where id=$row->sub_id"));
                $subtenant_type_idval = $subtenant_type_id[0]->subtenant_type_id;
                $deptid = \DB::select(\DB::raw("select parent_id from subtenant where id=$supervision"));
                $deptval = $deptid[0]->parent_id;

                if ($subtenant_type_idval == 9) {
                    $section = $supervision;
                    $sectionname = $supervisionname;
                }
                $kpi_perfsum = (number_format($row->perf_sum_w * 100, 2));

                if ($debug_mode == true) {
                    if ($kpi_perfsum > 100) {
                        $kpi_perfsum = 100;
                        $debug_show_icon = 1;
                        $debug_show_icon_perf_tool = '<i style="color:#ffffff;margin-left:10px;margin-right:10px;" class="fas fa-exclamation-triangle fa-1x"></i>';

                    } else {
                        $debug_show_icon = 0;
                        $debug_show_icon_perf_tool = '';
                    }


                }
                if ($kpi_perfsum >= 40 && $kpi_perfsum <= 60) {
                    $kpi_color = 1;

                } else {
                    $kpi_color = 0;
                }

//                if($supervision==''){
//                    $supervision=$dept;
//                    $deptidid = \DB::select(\DB::raw("select parent_id from subtenant where id=$dept"));
//                    $deptidval = $deptidid[0]->parent_id;
//                    $sectordid = \DB::select(\DB::raw("select parent_id from subtenant where id=$deptidval"));
//                    $sectoridval = $sectordid[0]->parent_id;
//                    if($supervision) {
//
//                        $sectorlink = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sectoridval . '>Show Section Report</a></div>';
//                        $sectorlinkar = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sectoridval . '>عرض أداء القسم</a></div>';
//
//                        $superlinkar = '<div style="float:left" style="color:#ffffff" ><a style="margin-left: 500px;color:#ffffff;font-size:16px;" href=\'. "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept" .\'> عرض أداء المراقبة </a></div>\'<br><div style="float:left" style="color:#ffffff" ><a style="margin-left: 500px;color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,dept" . '> عرض أداء المراقبة </a></div>';
//                        $superlink='';
//
//
//                    }
//                }

                if ($supervision) {
                    if ($this->arraycount($supervisionarray, $supervision) == 1) {
                        $sectorlink = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . '>Show Supervision Report</a><br><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . '>Show Section Report</a></div>';
                        $sectorlinkar = '<div style="color:#ffffff;margin-top: 20px;margin-left: -120px;" ><a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . '>عرض أداء المراقبة</a><br><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . '>عرض أداء القسم</a></div>';


                    } else {
                        $superlink = '';
                        $superlinkar = '';
                    }

                }
                $kpid1[] = [

                    'supervision' => $supervision,

                    'section' => $section,
                ];
                if ($debug_progmode == 0) {
                    if ($row->prog_sum_w < 0) {
                        $kpi_progsum = 0;
                        $debug_show_icon_prog_tool = 1;
                        $debug_show_icon_progic = '<i style="color:#ffffff;margin-left:10px;margin-right:10px;" class="fas fa-exclamation-triangle fa-1x"></i>';

                    } else {
                        $kpi_progsum = (number_format($row->prog_sum_w * 100, 2));

                        $debug_show_icon_prog_tool = 0;
                        $debug_show_icon_progic = '';

                    }
                }

                $kpishowdata[] = [
                    'Category' => 'deptreport',
                    'supervision' => $supervision,
                    'supervisionname' => $supervisionname,
                    'section' => $section,
                    'sectionname' => $sectionname,
                    'kpi_importancesum' => $row->kpi_importance_sum,
                    'kpi_perfsum' => $kpi_perfsum,
                    'kpi_progsum' => $kpi_progsum,
                    'calc_level' => $row->calc_level,
                    'kpicount' => $row->kpi_count,
                    'unitcount' => $row->unit_count,
                    "mtp" => $mtp,
                    "asofperiod" => $asofperiod,
                    "yearno" => $yearno,
                    "type" => $type,
                    "debug_show_icon" => $debug_show_icon,
                    "link" => "/sectionperformance" . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $deptval . ',supervision=' . $supervision,
                    "supervisionlink" => "/departmentperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector",
                    "kpi_color" => $kpi_color,
                    "sectorlink" => $sectorlink,
                    "sectorlinkar" => $sectorlinkar,
                    "debug_show_icon_prog_tool" => $debug_show_icon_prog_tool,
                    "debug_show_icon_progic" => $debug_show_icon_progic,
                    "debug_show_icon_perf_tool" => $debug_show_icon_perf_tool
                ];


                $supervision = '';
                $section = '';
                $supervisionname = '';
                $sectionname = '';
                $dataarray = [];


            }
            $unitperformance = $dptperfprog[0]->perf_sum_w;
            $unitprogress = $dptperfprog[0]->prog_sum_w;

            $unitperformance = (number_format($unitperformance * 100, 2));
            $unitprogress = (number_format($unitprogress * 100, 2));
            $kpibestperforming = max($kpiperfarray);
            $kpibestperforming = (number_format($kpibestperforming * 100, 2));
            if ($debug_mode == true) {
                if ($kpibestperforming > 100) {
                    $kpibestperforming = 100;
                    $debug_show_icon_best = 1;
                } else {
                    $debug_show_icon_best = 0;
                }

            }
            $kpileastperforming = min($kpiperfarray);
            $kpileastperforming = (number_format($kpileastperforming * 100, 2));
            if ($debug_mode == true) {
                if ($kpileastperforming > 100) {
                    $kpileastperforming = 100;
                    $debug_show_icon_least = 1;
                } else {
                    $debug_show_icon_least = 0;
                }

            }
        }
        if ($debug_progmode == 0) {
            if ($unitprogress < 0) {
                $unitprogress = 0;
                $debug_show_icon_prog = 1;
            } else {
                $unitprogress = $unitprogress;
                $debug_show_icon_prog = 0;
            }
        }

        header('Content-type: application/json');
//die();
        if ($kpishowdata) {
            if ($type != 1) {
                return response()->json([
                    "code" => 200,
                    "kpid1" => $kpid1,
                    "kpishowdata" => $kpishowdata,
                    "unitperformance" => $unitperformance,
                    "unitprogress" => $unitprogress,
                    "kpi_count" => $kpitotcount,
                    'unitcount' => $row->unit_count,
                    "kpistrategic_perf" => $kpistrategic_perf,
                    "kpistrategic_prog" => $kpistrategic_prog,
                    "kpistrategic_kpi" => $kpistrategic_kpi,
                    "kpibestperforming" => $kpibestperforming,
                    "kpileastperforming" => $kpileastperforming,
                    "performingcount" => $performingkpicount,
                    "nonperformingcount" => $nonperformingkpicount,
                    "kpistrategic_sector" => $kpistrategic_sector,
                    "debug_show_icon_best" => $debug_show_icon_best,
                    "debug_show_icon_least" => $debug_show_icon_least,
                    "debug_show_icon_prog" => $debug_show_icon_prog,
                    "debug_progmode" => $debug_progmode,
                    "debug_show_icon_progstrategic" => $debug_show_icon_progstrategic,


                ]);
            } else {
                return response()->json([
                    "code" => 200,
                    "kpid1" => $kpid1,
                    "kpishowdata" => $kpishowdata,
                    'unitcount' => $row->unit_count,
                    "unitperformance" => $unitperformance,
                    "unitprogress" => $unitprogress,
                    "kpi_count" => $kpitotcount,
                    "kpistrategic_perf" => $kpistrategic_perfarr,
                    "kpistrategic_prog" => $kpistrategic_progarr,
                    "kpistrategic_kpi" => $kpistrategic_kpiarr,
                    "kpibestperforming" => $kpibestperforming,
                    "kpileastperforming" => $kpileastperforming,
                    "performingcount" => $performingkpicount,
                    "nonperformingcount" => $nonperformingkpicount,
                    "kpistrategic_sector" => $kpistrategic_sector,
                    "kpistrategicvalues" => $kpistrategicvalues,
                    "debug_show_icon_best" => $debug_show_icon_best,
                    "debug_show_icon_least" => $debug_show_icon_least,
                    "debug_show_icon_prog" => $debug_show_icon_prog,
                    "debug_progmode" => $debug_progmode,
                    "debug_show_icon_progstrategic" => $debug_show_icon_progstrategic,
                    "debug_show_icon_prog_tool" => $debug_show_icon_prog_tool,
                    "debug_show_icon_progic" => $debug_show_icon_progic

                ]);
            }
        }

        return response()->json(["code" => 400]);
    }


    public function loadSubTenants($id)
    {
        //$subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)) from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '-'), '>', s.name), s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id) select id, name from cte order by path"));
        $subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '_') from subtenant where parent_id = $id and subtenant_type_id !=6 UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('_', 50), $id, '', CONCAT(id, '') from subtenant where parent_id = $id and subtenant_type_id !=6) select id, name from cte order by path"));


        // $subtenants = \DB::select(\DB::raw("select id, name from subtenant where parent_id = $id and subtenant_type_id=4"));
        foreach ($subtenants as $subtenant) {
//            $subtenant_type_id = \DB::select(\DB::raw("select subtenant_type_id from subtenant where id=$subtenant->id"));
//            $subtenant_type_idval = $subtenant_type_id[0]->subtenant_type_id;
            //echo "in";
            $word = "==>";

            if (strpos($subtenant->name, $word) !== false) {
//               //echo $subtenant->name;
                unset($subtenant->name);
                unset($subtenant->id);
                unset($subtenant);

            }
//
//
        }
////        var_dump($subtenants);
////die();
        //  $subtenants=  array_filter($subtenants);
        foreach ($subtenants as $key => $val) {
            if ($val === null || $val === '')
                unset($subtenants[$key]);
        }
        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $subtenants
            ]);
        }
    }

    public function getsubtenanttype($value)
    {
        $subtenant = $value;
        $subtenant_type_id = \DB::select(\DB::raw("select subtenant_type_id from subtenant where id=$value"));
        $subtenant_type_idval = $subtenant_type_id[0]->subtenant_type_id;
        if ($subtenant_type_idval) {
            return response()->json([
                "code" => 200,
                "subtenanttype" => $subtenant_type_idval
            ]);
        }
    }

    public function arraycount($array, $value)
    {
        $counter = 0;
        foreach ($array as $thisvalue) /*go through every value in the array*/ {
            if ($thisvalue === $value) { /*if this one value of the array is equal to the value we are checking*/
                $counter++; /*increase the count by 1*/
            }
        }
        return $counter;
    }


}

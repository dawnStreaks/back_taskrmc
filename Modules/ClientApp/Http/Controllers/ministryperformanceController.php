<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class ministryperformanceController extends Controller
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

        $section = $valuearray[0];
        $mtp = $valuearray[1];
        $asofperiod = $valuearray[2];
        $yearno = $valuearray[3];

        $deptid = \DB::select(\DB::raw("select parent_id from subtenant where id=$section"));
        $deptval = $deptid[0]->parent_id;

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
                "link" => "gaugechart/" . $row->kpi_id . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . ",fromsection," . $sectorval,
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
        $deptsuperlinkar = $deptsuperlink = '';

        $valuearray = explode(",", $value);

        $dept = $valuearray[0];
        $mtp = $valuearray[1];
        $asofperiod = $valuearray[2];
        $yearno = $valuearray[3];
        $type = $valuearray[4];
        $kpistrategic_sector = [];

        if ($mtp == 'null') {
            $mtp = 4;
        }
        if ($dept == 'null') {
            $dept = 2;
        }

        $tenantId = env('TENANT_ID') ? env('TENANT_ID') : 1;
        \DB::statement("set @tenant_id := $tenantId");
        \DB::statement("set @mtp_id:= $mtp");
        \DB::statement("set @year_no:= $yearno");
        \DB::statement("set @period_name := '$asofperiod'");


        $getKpiPerfProg = \DB::select(\DB::raw("select
		ob.id as target_id,
		ob.name target_name,
		kd.id as kpi_id,
		kd.name as kpi_name,
		kd.symbol as kpi_symbol,
		case lower(@period_name)
				when 'q1' then kvs.q1_perf
				when 'q2' then kvs.q2_perf
				when 'q3' then kvs.q3_perf
				when 'q4' then kvs.q4_perf
				when 'h1' then kvs.h1_perf
				when 'h2' then kvs.h2_perf
				when 'y' then kvs.y_perf
				else null
		end as kpi_perf,
		case lower(@period_name)
				when 'q1' then kvs.q1_prog
				when 'q2' then kvs.q2_prog
				when 'q3' then kvs.q3_prog
				when 'q4' then kvs.q4_prog
				when 'h1' then kvs.h1_prog
				when 'h2' then kvs.h2_prog
				when 'y' then kvs.y_prog
				else null
		end as kpi_prog,
		case when (not exists (
				select 1 from kpi_values kv where
						kv.kpi_target_id = kt.id and
						kv.actual_value is null and
						((kv.target_month <=
								(case lower(@period_name)
										when 'q1' then 3
										when 'q2' then 6
										when 'q3' then 9
										when 'q4' then 12
										when 'h1' then 6
										when 'h2' then 12
										when 'y' then 12
								end)
								and kv.target_year = @year_no) or (kv.target_year < @year_no))
						)) then 1 else 0
		end as kpi_up_to_date, /**1: is up_to_date, 0: is not**/
		kd.importance as weight,
		sub.id as sub_id,
		sub.name as sub_name,
		kt.mtp_id
		from
		kpi_values_stats kvs, kpi_target kt, kpi_def kd, subtenant sub, objective ob
		where
		sub.tenant_id = @tenant_id and
		sub.subtenant_type_id = 3 and /**for the sector type**/
		sub.id = kd.child_subtenant_id and
		kt.kpi_id = kd.id and
		kvs.kpi_target_id = kt.id and
		kt.mtp_id = @mtp_id and
		kd.scope_table = 'objective' and
		kd.scope_id = ob.id and
		kvs.year_no = @year_no
		order by ob.id, kd.symbol asc
;"));
        $resultPerfProg = [];
        foreach ($getKpiPerfProg as $perfprog) {
            $resultPerfProg[$perfprog->target_id][] = $perfprog;
        }

        $i = 0;
        $valsperfprog = [];
        foreach ($resultPerfProg as $prefprogvalue) {
            $kpiPerfCount = $kpiProgCount = 0;
            $kpiName = '';
            $job = 0;
            $kpiTargetName = [];
            foreach ($prefprogvalue as $prefandprog) {
                $kpiTargetName[$job]['kpi_name'] = $prefandprog->kpi_name;
                $kpiTargetName[$job]['kpi_perf'] = number_format($prefandprog->kpi_perf * 100, 2);
                $kpiTargetName[$job]['kpi_prog'] = number_format($prefandprog->kpi_prog * 100, 2);
                $kpiName = $prefandprog->target_name;
                $kpiPerfCount = $kpiPerfCount + $prefandprog->kpi_perf;
                $kpiProgCount = $kpiProgCount + $prefandprog->kpi_prog;
                $job++;
            }
            $valsperfprog[$i]['kpi_names'] = $kpiTargetName;
            $valsperfprog[$i]['target_name'] = $kpiName;
            $valsperfprog[$i]['kpi_perf'] = number_format($kpiPerfCount / $job * 100, 2);
            $valsperfprog[$i]['kpi_prog'] = number_format($kpiProgCount / $job * 100, 2);
            $i++;
        }

        if ($job > 3) {
            $countPerfProgKPI = 3;
        } elseif ($job > 2) {
            $countPerfProgKPI = 4;
        } else {
            $countPerfProgKPI = 6;
        }

        $tenants = \DB::select(\DB::raw("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3"));
        if ($tenants) {
            $tenst = [];
            foreach ($tenants as $tena) {
                $tenst[] = $tena->id;
            }
        }

        $pieChart = \DB::select(\DB::raw("call p_unit_perf_prog_recursive(2, $mtp, '$asofperiod', $yearno);"));
        $pieChartArray = [];
        $bubbleChartArray = [];
        $kpiBestLeastVals = [];
        $ii = 0;
        $jj = 0;
        foreach ($pieChart as $chartData) {
            if (in_array($chartData->sub_id, $tenst)) {
                if ($chartData->perf_sum_w > 0) {
                    $kpiBestLeastVals[] = ($chartData->perf_sum_w) ? number_format($chartData->perf_sum_w * 100, 2) : 0;
                }
                $pieChartArray[$chartData->sub_id]['sub_id'] = $chartData->sub_id;
                $pieChartArray[$chartData->sub_id]['sub_name'] = $chartData->sub_name;
                $pieChartArray[$chartData->sub_id]['performance'] = ($chartData->perf_sum_w) ? number_format($chartData->perf_sum_w * 100, 2) : 0;
                $ii++;
            }

            if (in_array($chartData->sub_id, $tenst) && $chartData->kpi_count != '') {
                $tenantsName = \DB::select(\DB::raw("select id, short_name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3 and id=$chartData->sub_id"));

                $prog_sum_w = ($chartData->prog_sum_w) ? number_format($chartData->prog_sum_w * 100, 2) : 0;
                if (($prog_sum_w) && $prog_sum_w > 0) {
                    $x = number_format($chartData->prog_sum_w * 100, 2);
                }
                if (($prog_sum_w) && $prog_sum_w > 200) {
                    $x = 200;
                }

                if (($prog_sum_w) && $prog_sum_w < 0 || $prog_sum_w == 0) {
                    $x = 0;
                }

                $perf_sum_w = ($chartData->perf_sum_w) ? number_format($chartData->perf_sum_w * 100, 2) : 0;
                if (($perf_sum_w) && $perf_sum_w > 0) {
                    $y = number_format($chartData->perf_sum_w * 100, 2);
                    $size = number_format($perf_sum_w / 40, 2);
                }
                if (($perf_sum_w) && $perf_sum_w > 200) {
                    $y = 100;
                    $size = 4;
                }

                if (($perf_sum_w) && $perf_sum_w < 0 || $perf_sum_w == 0) {
                    $y = 0;
                    $size = 0;
                }

                if ($y != 0) {
                    $bubbleChartArray[$jj]['x'] = $x;
                    $bubbleChartArray[$jj]['y'] = $y;
                    $bubbleChartArray[$jj]['size'] = $size;
                    $bubbleChartArray[$jj]['short'] = $tenantsName[0]->short_name;;
                    $bubbleChartArray[$jj]['text'] = $chartData->sub_name;
                    $jj++;
                }
            }
        }
        $kpisectorBest = max($kpiBestLeastVals);
        //$kpisectorBest = (number_format($kpisectorBest * 100, 2));
        if ($debug_mode == true) {
            if ($kpisectorBest > 100) {
                $kpisectorBest = 100;
                $debug_show_icon_sector_best = 1;
            } else {
                $debug_show_icon_sector_best = 0;
            }

        }
        $kpisectorLeast = min($kpiBestLeastVals);
        //$kpisectorLeast = (number_format($kpisectorLeast * 100, 2));
        if ($debug_mode == true) {
            if ($kpisectorLeast > 100) {
                $kpisectorLeast = 100;
                $debug_show_icon_sector_least = 1;
            } else {
                $debug_show_icon_sector_least = 0;
            }

        }

        $dash_val = \DB::select(\DB::raw("call p_unit_perf_prog_recursive(
				$dept, -- a_subtenant_id: argument: (the parent subtenant id)
				/**
					-- Description: same as used with the old query:
					-- 1) if nothing is selected in the filter (i.e. null sector, null org unit)
									-> put the argument = 2 (it's the parent id of the sectors)
					-- 2) if sector in the filter is selected, and no org unit
									-> put the argument = id of the sector
					-- 3) if sector in the filter is selected, and org unit is selected
									-> put the argument = id of the org unit
				**/
				$mtp, -- a_mtp_id: argument
				'Y', -- fixed
				1 -- fixed
				);"));

        $mainVals = [];

        $activeCount = $notActiveCount = $uptodateCount = $notUpdatetodate = 0;
        $efficiencyCount = $strategyCount = $highCount = $midCount = $lowCount = 0;
        $activeCountSector = $activeCountStrategy = 0;
        $activeSectorName = '';
        $j = 0;
        foreach ($dash_val as $vals) {
//            if ($vals->sub_id != 2) {
//echo $vals->kpi_count;
            //&& $vals->kpi_count != ''
            $debug_progmode = false;
            if (in_array($vals->sub_id, $tenst)) {
                $vals->perf_sum_w = ($vals->perf_sum_w) ? (number_format($vals->perf_sum_w * 100, 2)) : 0;
                if ($vals->prog_sum_w < 0 && !$debug_progmode) {
                    $vals->prog_sum_w = 0;
                    $vals->show_icon = true;
                } else {
                    $vals->show_icon = false;
                    $vals->prog_sum_w = ($vals->prog_sum_w) ? (number_format($vals->prog_sum_w * 100, 2)) : 0;
                }
                $vals->by_strategy = ($vals->kpi_count - $vals->kpi_eff);
                $vals->not_uptodate = ($vals->kpi_count - $vals->kpi_up_to_date);
                $vals->not_active = ($vals->kpi_count - $vals->kpi_active);
                if($vals->perf_sum_w>0) {
                    $vals->sectorlink = "/departmentperformance" . "?query=" . $vals->sub_id . "," . $mtp . "," .
                        $asofperiod . "," . $yearno . "," . $dept . ",2,dept,fromministry";
                }
                else{
                    $vals->sectorlink='#';
                }
                $mainVals[] = $vals;
                $j++;
            }
        }

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
                    /*"departmentlink" => "/departmentperformance" . "?query=section=" . $section . "," . $mtp . "," .
                        $asofperiod . "," . $yearno . "," . $dept,*/
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
                    "departmentlink" => "/departmentperformance" . "?query=section=" . $section . "," . $mtp . "," .
                        $asofperiod . "," . $yearno . "," . $dept,
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
                    "departmentlink" => "/departmentperformance" . "?query=section=" . $section . "," . $mtp . "," .
                        $asofperiod . "," . $yearno . "," . $dept,
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

        if ($type == 3) {
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
            $departmentarray = [];
            foreach ($dptperfprog as $row) {
//                if ($row->calc_level = 1 && isset($row->perf_sum_w)) {
//                    $kpiperfarray[] = $row->perf_sum_w;
//
//                }

                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);
                if (isset($dataarray[3])) {
                    $supervisionarray[] = $dataarray[3];
                }
                if (isset($dataarray[2])) {
                    $departmentarray[] = $dataarray[2];
                }
            }

            foreach ($dptperfprog as $row) {

                $ministry = $dept = $sector = $supervision = $section = '';
                $sectorname = $ministryname = $deptname = $supervisionname = $sectionname = '';
                if ($row->calc_level = 1 && isset($row->perf_sum_w)) {
                    $kpiperfarray[] = $row->perf_sum_w;

                }
//              var_dump($dptperfprog);
//                die();

                $subpath = $row->sub_path;
                $dataarray = explode(",", $subpath);
//echo "<pre>";
  //              var_dump($dataarray);
                $ministry = preg_replace('/[^A-Za-z0-9]/', '', $dataarray[0]);
                $ministrynameval = \DB::select(\DB::raw("select name from subtenant where id=$ministry"));
                $ministryname = $ministrynameval[0]->name;
                if (!empty($dataarray[1])) {
                    $sector = $dataarray[1];
                    $sectornameval = \DB::select(\DB::raw("select name from subtenant where id=$sector"));
                    $sectorname = $sectornameval[0]->name;
                    $secoreArray[] = $sector;
                    // $depts[] = $dept;
                }

                if (!empty($dataarray[2])) {
                    $dept = $dataarray[2];
                    $deptnameval = \DB::select(\DB::raw("select name from subtenant where id=$dept"));
                    $deptname = $deptnameval[0]->name;
                    $depts[] = $dept;

                }

                if (!empty($dataarray[3])) {
                    $supervision = $dataarray[3];
                    $supervisionnameval = \DB::select(\DB::raw("select name from subtenant where id=$supervision"));
                    $supervisionname = $supervisionnameval[0]->name;

                }
                if (!empty($dataarray[4])) {
                    $section = $dataarray[4];
                    $sectionnameval = \DB::select(\DB::raw("select name from subtenant where id=$section"));
                    $sectionname = $sectionnameval[0]->name;

                }

                //echo $ministry."->".$sector."->".$dept."->".$supervision."->".$section;
                //echo PHP_EOL;
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

                    'ministry' => $ministry,
                    'sector' => $sector,
                    'supervision' => $supervision,
                    "dept" => $dept,
                    'section' => $section,
                ];
                if ($supervision) {
                    if ($this->arraycount($supervisionarray, $supervision) == 1) {
                        $linkssss = "/departmentperformance" . "?query=section=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept;
                        $superlink = '<div style="color:#ffffff" ><a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . '>Show Supervision Report</a><br><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . '>Show Section Report</a></div>';
                        $superlinkar = '<div style="color:#ffffff;margin-top: 20px;margin-left: -120px;" ><a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . '>عرض أداء المراقبة</a><br><a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . '>عرض أداء القسم</a></div>';


                    } else {
                        $superlink = '';
                        $superlinkar = '';
                    }


                }
                if ($dept) {
                    if ($this->arraycount($departmentarray, $dept) == 1) {
                        $linkssss = "/departmentperformance" . "?query=section=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept;
                        $deptsuperlink = '<div style="color:#ffffff" >
<a style="color:#ffffff;font-size:16px;" href=' . $linkssss . ',fromministry'.'>Show Department Report</a><br>
<a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . ',fromministry'.'>Show Supervision Report</a><br>
<a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector . ',fromministry'.'>Show Section Report</a></div>';

                        $deptsuperlinkar = '<div style="color:#ffffff;margin-top: 20px;margin-left: -120px;" >
<a style="color:#ffffff;font-size:16px;" href=' . $linkssss . '>Show Department Report</a><br>
<a style="color:#ffffff;font-size:16px;" href=' . "/departmentperformance" . "?query=" . $dept . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector .  ',fromministry'.'>عرض أداء المراقبة</a><br>
<a style="color:#ffffff;font-size:16px;" href=' . "/sectionperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",sector=" . $sector .  ',fromministry'.'>عرض أداء القسم</a>
</div>';


                    } else {
                        $deptsuperlink = '';
                        $deptsuperlinkar = '';
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
                    'ministryname' => 'وزارة المالية',//$ministryname,
                    'ministry' => $ministry,
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
                    "link" => "/sectionperformance" . "?query=" . $section . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ',sector=' . $sector . ',fromministry',
                    "supervisionlink" => "/departmentperformance" . "?query=" . $supervision . "," . $mtp . "," . $asofperiod . "," . $yearno . "," . $dept . ",2,sector," . $sector . ',fromministry',
                    "departmentlink" => "/departmentperformance" . "?query=section=" . $section . "," . $mtp . "," .
                        $asofperiod . "," . $yearno . "," . $dept. ',fromministry',
                    'kpi_color' => $kpi_color,
                    "button" => $superlink,
                    "buttonar" => $superlinkar,
                    "debug_show_icon_prog_tool" => $debug_show_icon_prog_tool,
                    "debug_show_icon_progic" => $debug_show_icon_progic,
                    "debug_show_icon_perf_tool" => $debug_show_icon_perf_tool,
                    "deptsuperlink" => $deptsuperlink,
                    "deptsuperlinkar" => $deptsuperlinkar


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

        if ($debug_progmode == 0) {
            if ($unitprogress < 0) {
                $unitprogress = 0;
                $debug_show_icon_prog = 1;
            } else {
                $unitprogress = $unitprogress;
                $debug_show_icon_prog = 0;
            }
        }

        //var_dump($kpid1);
       //die;
        header('Content-type: application/json');
        if ($kpishowdata) {
            if ($type != 1) {
                return response()->json([
                    "code" => 200,
                    "kpid1" =>$kpid1,
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

                    "sectorData" => $mainVals,
                    "getKpiPerfProg" => $valsperfprog,
                    "countPerfProgKPI" => $countPerfProgKPI,
                    "pieChartArray" => $pieChartArray,
                    "bubbleChartArray" => $bubbleChartArray,
                    "kpisectorbest" => $kpisectorBest,
                    "kpisectorleast" => $kpisectorLeast,
                    "debug_show_icon_sector_best" => $debug_show_icon_sector_best,
                    "debug_show_icon_sector_least" => $debug_show_icon_sector_least,


                ]);
            } else {
                return response()->json([
                    "code" => 200,
                    "kpid1" =>$kpid1,
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
                    "debug_show_icon_progic" => $debug_show_icon_progic,

                    "sectorData" => $mainVals,
                    "getKpiPerfProg" => $valsperfprog,
                    "countPerfProgKPI" => $countPerfProgKPI,
                    "pieChartArray" => $pieChartArray,
                    "bubbleChartArray" => $bubbleChartArray,
                    "kpisectorbest" => $kpisectorBest,
                    "kpisectorleast" => $kpisectorLeast,
                    "debug_show_icon_sector_best" => $debug_show_icon_sector_best,
                    "debug_show_icon_sector_least" => $debug_show_icon_sector_least,

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

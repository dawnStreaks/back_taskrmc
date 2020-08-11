<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\ObjectModel;
use Modules\ClientApp\Http\Requests\ObjectModelStore;
use Modules\ClientApp\Http\Requests\ObjectModelUpdate;
use Spatie\Permission\Models\Permission;

class maintenanceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        //$this->middleware('permission:maintenance-list');
        //$this->middleware('permission:maintenance-list');
        //$this->middleware('permission:maintenance-create', ['only' => ['store']]);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function loadMaintenance()
    {

        \DB::select(\DB::raw("set @mtp_id := 4"));
        \DB::select(\DB::raw("set @sub_id:= 2"));

        $maintenanceData = \DB::select(\DB::raw("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
		from subtenant where
        id = @sub_id
	UNION ALL
    -- This is the recursive part: It joins to cte
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
		from subtenant s
        inner join cte c on s.parent_id = c.id
	)
	-- select id, name, subtenant_type, parent_id
	select 	kt.kpi_id kpi_id,
							kt.id as kpi_target_id,
							kd.symbol as kpi_symbol,
							kv.target_year,
							kv.target_month,
							kv.actual_value
	from cte, kpi_target kt, kpi_def kd, kpi_values kv where
	kd.child_subtenant_id = cte.id and
	kt.kpi_id = kd.id and
	kt.mtp_id = @mtp_id and
	kv.kpi_target_id = kt.id and
	kv.actual_value is not null
	order by kd.symbol, kd.id, kv.target_year, kv.target_month;"));

        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $month = date('m', strtotime($currentmtpstartdate));
        $startyear = date('Y', strtotime($currentmtpstartdate));
        $startyear = (int)$startyear;

        $maintenanceObject = [];
        $i = 0;
        foreach ($maintenanceData as $maintenance) {
            $year1q1 = $year1q2 = $year1q3 = $year1q4 = false;
            $year2q1 = $year2q2 = $year2q3 = $year2q4 = false;
            $year3q1 = $year3q2 = $year3q3 = $year3q4 = false;

            $kpi_id = $maintenance->kpi_id;
            $kpi_target_id = $maintenance->kpi_target_id;
            $target_year = $maintenance->target_year;
            $target_month = $maintenance->target_month;
            $actual_value = $maintenance->actual_value;
            $kpi_symbol = $maintenance->kpi_symbol;
            //if ($i <= 47) {
            $maintenanceObject[$i]['id'] = $i + 1;
            $maintenanceObject[$i]['kpi_id'] = $kpi_id;
            $maintenanceObject[$i]['kpi_target_id'] = $kpi_target_id;
            $maintenanceObject[$i]['kpi_symbol'] = $kpi_symbol;

            $month = $target_month;
            $year = $target_year;
            $month = (int)$month;
            if ($month == 4 || $month == 5 || $month == 6) {
                if ($year == 1) {
                    $year1q2 = true;
                    //echo "year1q1".$year1q1;
                }

                if ($year == 2) {
                    $year2q2 = true;
                    //echo "year2q1".$year2q1;
                }
                if ($year == 3) {
                    $year3q2 = true;
                    // echo "year3q1".$year3q1;
                }
            }
            if ($month == 7 || $month == 8 || $month == 9) {
                if ($year == 1) {
                    $year1q3 = true;
                    // echo "year1q2".$year1q2;
                }
                if ($year == 2) {
                    $year2q3 = true;
                    //  echo "$year2q2".$year2q2;
                }
                if ($year == 3) {
                    $year3q3 = true;
                    //   echo "year3q2".$year3q2;
                }
            }
            if ($month == 10 || $month == 11 || $month == 12) {
                if ($year == 1) {
                    $year1q4 = true;
                    // echo "year1q3".$year1q3;

                }
                if ($year == 2) {
                    $year2q4 = true;
                    // echo "year2q3".$year2q3;
                }
                if ($year == 3) {
                    $year3q4 = true;
                    //echo "year3q3".$year3q3;
                }
            }
            if ($month == 1 || $month == 2 || $month == 3) {
                if ($year == 1) {
                    $year1q1 = true;
                    //echo "year1q4".$year1q4;
                }
                if ($year == 2) {
                    $year2q1 = true;
                    // echo "year2q4".$year2q4;
                }
                if ($year == 3) {
                    $year3q1 = true;
                    // echo "year3q4".$year3q4;
                }
            }

            $formuladata = \DB::select(\DB::raw("select kpi_def.name,kpi_def.value_explanation,kpi_performance_type.formula,kpi_performance_type.factor_1,kpi_performance_type.factor_2 from kpi_def join kpi_performance_type on kpi_def.value_explanation=kpi_performance_type.id where kpi_def.id=$kpi_id"));

            $maintenanceObject[$i]['name'] = $formuladata[0]->name;

            \DB::select(\DB::raw("set @period_q := 3"));
            \DB::select(\DB::raw("set @period_h:= 6"));
            \DB::select(\DB::raw("set @period_y:= 12"));
            \DB::select(\DB::raw("set @kpi_target_id := $kpi_target_id"));

            $valuestadata = \DB::select(\DB::raw("SELECT kvs.year_no,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_max_value,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_max_value,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_max_value,

	kvs.q1_value,
	kvs.q1_target,
	kvs.q2_value,
	kvs.q2_target,
	kvs.q3_value,
	kvs.q3_target,
	kvs.q4_value,
	kvs.q4_target,

	kvs.h1_value,
	kvs.h1_target,
	kvs.h2_value,
	kvs.h2_target,

	kvs.y_value,
	kvs.y_target

FROM kpi_values_stats kvs WHERE
	kvs.kpi_target_id = $kpi_target_id AND
    kvs.year_no = $target_year
ORDER BY kvs.year_no;"));
            // var_dump($valuestadata);


            foreach ($valuestadata as $valdata) {

                $year_no = $valdata->year_no;
                $q1value = $valdata->q1_value;
                $q2value = $valdata->q2_value;
                $q3value = $valdata->q3_value;
                $q4value = $valdata->q4_value;
                $h1value = $valdata->h1_value;
                $h2value = $valdata->h2_value;
                $yvalue = $valdata->y_value;

                $q1target = $valdata->q1_target;
                $q2target = $valdata->q2_target;
                $q3target = $valdata->q3_target;
                $q4target = $valdata->q4_target;
                $h1target = $valdata->h1_target;
                $h2target = $valdata->h2_target;
                $ytarget = $valdata->y_target;
                $formula = $formuladata[0]->formula;
                $qmin_value = $valdata->q_min_value;
                $qmax_value = $valdata->q_max_value;
                $qbase_value = $valdata->q_base_value;
                $hmin_value = $valdata->h_min_value;
                $hmax_value = $valdata->h_max_value;
                $hbase_value = $valdata->h_base_value;
                $ymin_value = $valdata->y_min_value;
                $ymax_value = $valdata->y_max_value;
                $ybase_value = $valdata->y_base_value;

                $factor_1 = $formuladata[0]->factor_1;
                $factor_2 = $formuladata[0]->factor_2;

                //  echo "the calculation is";

                $y_perf = $this->perfcalculate($yvalue, $formula, $ymin_value, $ymax_value, $ybase_value, $factor_1,
                    $factor_2, $ytarget);
                //  echo "yperf=".$y_perf."<br/>";
                $y_prog = $this->progfcalculate($yvalue, $ybase_value, $ytarget);
                // echo "$y_prog=".$y_prog."<br/>";

                $maintenanceObject[$i]['y_perf'] = (round($y_perf, 8));
                $maintenanceObject[$i]['y_prog'] = (round($y_prog, 8));
                if ($year1q1 == true || $year2q1 == true || $year3q1 == true) {

                    $q1_perf = $this->perfcalculate($q1value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q1target);
                    $q1_prog = $this->progfcalculate($q1value, $qbase_value, $q1target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);
                    $maintenanceObject[$i]['q1_perf'] = (round($q1_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q1_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h1_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h1_prog, 8));
                    /*if ($year1q1 == true && $year_no == 1) {
                        $maintenanceObject[$i]['y_perf'] = $y_perf;
                        $maintenanceObject[$i]['y_prog'] = $y_prog;
                    }
                    if ($year2q1 == true && $year_no == 2) {

                    }
                    if ($year3q1 == true && $year_no == 3) {

                    }*/

                }
                if ($year1q2 == true || $year2q2 == true || $year3q2 == true) {
                    $q2_perf = $this->perfcalculate($q2value, $formula, $qmin_value, $qmax_value, $qbase_value,
                        $factor_1, $factor_2, $q2target);
                    $q2_prog = $this->progfcalculate($q2value, $qbase_value, $q2target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);

                    $maintenanceObject[$i]['q1_perf'] = (round($q2_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q2_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h1_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h1_prog, 8));
                }
                if ($year1q3 == true || $year2q3 == true || $year3q3 == true) {
                    $q3_perf = $this->perfcalculate($q3value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q3target);
                    $q3_prog = $this->progfcalculate($q3value, $qbase_value, $q3target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);
                    $maintenanceObject[$i]['q1_perf'] = (round($q3_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q3_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h2_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h2_prog, 8));

                }
                if ($year1q4 == true || $year2q4 == true || $year3q4 == true) {

                    $q4_perf = $this->perfcalculate($q4value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q4target);
                    $q4_prog = $this->progfcalculate($q4value, $qbase_value, $q4target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);

                    $maintenanceObject[$i]['q1_perf'] = (round($q4_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q4_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h2_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h2_prog, 8));
                }
            }
            //}
            $i++;
        }
        if ($maintenanceObject) {
            return response()->json([
                "code" => 200,
                "maintenanceData" => $maintenanceObject
            ]);
        }

        return response()->json(["code" => 400]);
    }

    public function loadMaintenanceById($id, $mtp_id)
    {
        if ($mtp_id == 'null') {
            $mtp_id = 4;
        }
        if ($id == 'null') {
            $id = 2;
        }

        \DB::select(\DB::raw("set @mtp_id := $mtp_id"));
        \DB::select(\DB::raw("set @sub_id:= $id"));

        $maintenanceData = \DB::select(\DB::raw("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
		from subtenant where
        id = @sub_id
	UNION ALL
    -- This is the recursive part: It joins to cte
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
		from subtenant s
        inner join cte c on s.parent_id = c.id
	)
	-- select id, name, subtenant_type, parent_id
	select 	kt.kpi_id kpi_id,
							kt.id as kpi_target_id,
							kd.symbol as kpi_symbol,
							kv.target_year,
							kv.target_month,
							kv.actual_value
	from cte, kpi_target kt, kpi_def kd, kpi_values kv where
	kd.child_subtenant_id = cte.id and
	kt.kpi_id = kd.id and
	kt.mtp_id = @mtp_id and
	kv.kpi_target_id = kt.id and
	kv.actual_value is not null
	order by kd.symbol, kd.id, kv.target_year, kv.target_month;"));

        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $month = date('m', strtotime($currentmtpstartdate));
        $startyear = date('Y', strtotime($currentmtpstartdate));
        $startyear = (int)$startyear;

        $maintenanceObject = [];
        $i = 0;
        foreach ($maintenanceData as $maintenance) {

            $year1q1 = $year1q2 = $year1q3 = $year1q4 = false;
            $year2q1 = $year2q2 = $year2q3 = $year2q4 = false;
            $year3q1 = $year3q2 = $year3q3 = $year3q4 = false;

            $kpi_id = $maintenance->kpi_id;
            $kpi_target_id = $maintenance->kpi_target_id;
            $target_year = $maintenance->target_year;
            $target_month = $maintenance->target_month;
            $actual_value = $maintenance->actual_value;
            $kpi_symbol = $maintenance->kpi_symbol;
            //if ($i <= 47) {
            $maintenanceObject[$i]['id'] = $i + 1;
            $maintenanceObject[$i]['kpi_id'] = $kpi_id;
            $maintenanceObject[$i]['kpi_target_id'] = $kpi_target_id;
            $maintenanceObject[$i]['kpi_symbol'] = $kpi_symbol;

            $month = $target_month;
            $year = $target_year;
            $month = (int)$month;
            if ($month == 4 || $month == 5 || $month == 6) {
                if ($year == 1) {
                    $year1q2 = true;
                    //echo "year1q1".$year1q1;
                }

                if ($year == 2) {
                    $year2q2 = true;
                    //echo "year2q1".$year2q1;
                }
                if ($year == 3) {
                    $year3q2 = true;
                    // echo "year3q1".$year3q1;
                }
            }
            if ($month == 7 || $month == 8 || $month == 9) {
                if ($year == 1) {
                    $year1q3 = true;
                    // echo "year1q2".$year1q2;
                }
                if ($year == 2) {
                    $year2q3 = true;
                    //  echo "$year2q2".$year2q2;
                }
                if ($year == 3) {
                    $year3q3 = true;
                    //   echo "year3q2".$year3q2;
                }
            }
            if ($month == 10 || $month == 11 || $month == 12) {
                if ($year == 1) {
                    $year1q4 = true;
                    // echo "year1q3".$year1q3;

                }
                if ($year == 2) {
                    $year2q4 = true;
                    // echo "year2q3".$year2q3;
                }
                if ($year == 3) {
                    $year3q4 = true;
                    //echo "year3q3".$year3q3;
                }
            }
            if ($month == 1 || $month == 2 || $month == 3) {
                if ($year == 1) {
                    $year1q1 = true;
                    //echo "year1q4".$year1q4;
                }
                if ($year == 2) {
                    $year2q1 = true;
                    // echo "year2q4".$year2q4;
                }
                if ($year == 3) {
                    $year3q1 = true;
                    // echo "year3q4".$year3q4;
                }
            }

            $formuladata = \DB::select(\DB::raw("select kpi_def.value_explanation,kpi_def.name,kpi_performance_type.formula,kpi_performance_type.factor_1,kpi_performance_type.factor_2 from kpi_def join kpi_performance_type on kpi_def.value_explanation=kpi_performance_type.id where kpi_def.id=$kpi_id"));

            $maintenanceObject[$i]['name'] = $formuladata[0]->name;
            \DB::select(\DB::raw("set @period_q := 3"));
            \DB::select(\DB::raw("set @period_h:= 6"));
            \DB::select(\DB::raw("set @period_y:= 12"));
            \DB::select(\DB::raw("set @kpi_target_id := $kpi_target_id"));

            $valuestadata = \DB::select(\DB::raw("SELECT kvs.year_no,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_max_value,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_max_value,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_max_value,

	kvs.q1_value,
	kvs.q1_target,
	kvs.q2_value,
	kvs.q2_target,
	kvs.q3_value,
	kvs.q3_target,
	kvs.q4_value,
	kvs.q4_target,

	kvs.h1_value,
	kvs.h1_target,
	kvs.h2_value,
	kvs.h2_target,

	kvs.y_value,
	kvs.y_target

FROM kpi_values_stats kvs WHERE
	kvs.kpi_target_id = $kpi_target_id AND
    kvs.year_no = $target_year
ORDER BY kvs.year_no;"));
            // var_dump($valuestadata);


            foreach ($valuestadata as $valdata) {

                $year_no = $valdata->year_no;
                $q1value = $valdata->q1_value;
                $q2value = $valdata->q2_value;
                $q3value = $valdata->q3_value;
                $q4value = $valdata->q4_value;
                $h1value = $valdata->h1_value;
                $h2value = $valdata->h2_value;
                $yvalue = $valdata->y_value;

                $q1target = $valdata->q1_target;
                $q2target = $valdata->q2_target;
                $q3target = $valdata->q3_target;
                $q4target = $valdata->q4_target;
                $h1target = $valdata->h1_target;
                $h2target = $valdata->h2_target;
                $ytarget = $valdata->y_target;
                $formula = $formuladata[0]->formula;
                $qmin_value = $valdata->q_min_value;
                $qmax_value = $valdata->q_max_value;
                $qbase_value = $valdata->q_base_value;
                $hmin_value = $valdata->h_min_value;
                $hmax_value = $valdata->h_max_value;
                $hbase_value = $valdata->h_base_value;
                $ymin_value = $valdata->y_min_value;
                $ymax_value = $valdata->y_max_value;
                $ybase_value = $valdata->y_base_value;

                $factor_1 = $formuladata[0]->factor_1;
                $factor_2 = $formuladata[0]->factor_2;

                //  echo "the calculation is";

                $y_perf = $this->perfcalculate($yvalue, $formula, $ymin_value, $ymax_value, $ybase_value, $factor_1,
                    $factor_2, $ytarget);
                //  echo "yperf=".$y_perf."<br/>";
                $y_prog = $this->progfcalculate($yvalue, $ybase_value, $ytarget);
                // echo "$y_prog=".$y_prog."<br/>";

                $maintenanceObject[$i]['y_perf'] = (round($y_perf, 8));
                $maintenanceObject[$i]['y_prog'] = (round($y_prog, 8));
                if ($year1q1 == true || $year2q1 == true || $year3q1 == true) {

                    $q1_perf = $this->perfcalculate($q1value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1,
                        $factor_2, $q1target);
                    $q1_prog = $this->progfcalculate($q1value, $qbase_value, $q1target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1,
                        $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);
                    $maintenanceObject[$i]['q1_perf'] = (round($q1_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q1_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h1_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h1_prog, 8));
                    /*if ($year1q1 == true && $year_no == 1) {
                        $maintenanceObject[$i]['y_perf'] = $y_perf;
                        $maintenanceObject[$i]['y_prog'] = $y_prog;
                    }
                    if ($year2q1 == true && $year_no == 2) {

                    }
                    if ($year3q1 == true && $year_no == 3) {

                    }*/

                }
                if ($year1q2 == true || $year2q2 == true || $year3q2 == true) {
                    $q2_perf = $this->perfcalculate($q2value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q2target);
                    $q2_prog = $this->progfcalculate($q2value, $qbase_value, $q2target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);

                    $maintenanceObject[$i]['q1_perf'] = (round($q2_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q2_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h1_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h1_prog, 8));
                }
                if ($year1q3 == true || $year2q3 == true || $year3q3 == true) {
                    $q3_perf = $this->perfcalculate($q3value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q3target);
                    $q3_prog = $this->progfcalculate($q3value, $qbase_value, $q3target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);
                    $maintenanceObject[$i]['q1_perf'] = (round($q3_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q3_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h2_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h2_prog, 8));

                }
                if ($year1q4 == true || $year2q4 == true || $year3q4 == true) {

                    $q4_perf = $this->perfcalculate($q4value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q4target);
                    $q4_prog = $this->progfcalculate($q4value, $qbase_value, $q4target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);

                    $maintenanceObject[$i]['q1_perf'] = (round($q4_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q4_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h2_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h2_prog, 8));
                }
            }
            //}
            $i++;
        }
        if ($maintenanceObject) {
            return response()->json([
                "code" => 200,
                "maintenanceData" => $maintenanceObject
            ]);
        } elseif (count($maintenanceObject) == 0) {
            return response()->json([
                "code" => 200,
                "maintenanceData" => []
            ]);
        }

        return response()->json(["code" => 400]);
    }

    public function progfcalculate($value, $base_value, $targetvalue)
    {
        //echo $value.'=='.$base_value.'=='.$targetvalue;
        if ($targetvalue == $base_value) {
            $progress = 0;
        } else {
            $progress = ($value - $base_value) / ($targetvalue - $base_value);
        }
        return $progress;

    }


    public function perfcalculate($value, $formula, $mn, $mx, $base, $factor_1, $factor_2, $target)
    {
        $value = $value;
        $formula = $formula;
        $mn = ($mn) ? $mn : 0;
        $mx = ($mx) ? $mx : $target * 1.2;
        if (($mn - $mx) == 0) {
            return '0';
        }
        $base = $base;
        $factor_1 = $factor_1;
        $factor_2 = $factor_2;


        if ($formula) {
            $result = eval("return " . $formula . ";");
        }
        return $result;
    }

    public function updatedbKpivaluestates($id, $mtp_id)
    {
        if ($mtp_id == 'null') {
            $mtp_id = 4;
        }
        if ($id == 'null') {
            $id = 2;
        }

        \DB::select(\DB::raw("set @mtp_id := $mtp_id"));
        \DB::select(\DB::raw("set @sub_id:= $id"));

        $maintenanceData = \DB::select(\DB::raw("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
		from subtenant where
        id = @sub_id
	UNION ALL
    -- This is the recursive part: It joins to cte
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
		from subtenant s
        inner join cte c on s.parent_id = c.id
	)
	-- select id, name, subtenant_type, parent_id
	select 	kt.kpi_id kpi_id,
							kt.id as kpi_target_id,
							kd.symbol as kpi_symbol,
							kv.target_year,
							kv.target_month,
							kv.actual_value
	from cte, kpi_target kt, kpi_def kd, kpi_values kv where
	kd.child_subtenant_id = cte.id and
	kt.kpi_id = kd.id and
	kt.mtp_id = @mtp_id and
	kv.kpi_target_id = kt.id and
	kv.actual_value is not null
	order by kd.symbol, kd.id, kv.target_year, kv.target_month;"));

        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $month = date('m', strtotime($currentmtpstartdate));
        $startyear = date('Y', strtotime($currentmtpstartdate));
        $startyear = (int)$startyear;

        $maintenanceObject = [];
        $i = 0;
        foreach ($maintenanceData as $maintenance) {
            $year1q1 = $year1q2 = $year1q3 = $year1q4 = false;
            $year2q1 = $year2q2 = $year2q3 = $year2q4 = false;
            $year3q1 = $year3q2 = $year3q3 = $year3q4 = false;

            $kpi_id = $maintenance->kpi_id;
            $kpi_target_id = $maintenance->kpi_target_id;
            $target_year = $maintenance->target_year;
            $target_month = $maintenance->target_month;
            $actual_value = $maintenance->actual_value;
            $kpi_symbol = $maintenance->kpi_symbol;
            //if ($i < 4) {
            $maintenanceObject[$i]['id'] = $i + 1;
            $maintenanceObject[$i]['kpi_id'] = $kpi_id;
            $maintenanceObject[$i]['kpi_target_id'] = $kpi_target_id;
            $maintenanceObject[$i]['kpi_symbol'] = $kpi_symbol;

            $month = $target_month;
            $year = $target_year;
            $month = (int)$month;
            if ($month == 4 || $month == 5 || $month == 6) {
                if ($year == 1) {
                    $year1q2 = true;
                    //echo "year1q1".$year1q1;
                }

                if ($year == 2) {
                    $year2q2 = true;
                    //echo "year2q1".$year2q1;
                }
                if ($year == 3) {
                    $year3q2 = true;
                    // echo "year3q1".$year3q1;
                }
            }
            if ($month == 7 || $month == 8 || $month == 9) {
                if ($year == 1) {
                    $year1q3 = true;
                    // echo "year1q2".$year1q2;
                }
                if ($year == 2) {
                    $year2q3 = true;
                    //  echo "$year2q2".$year2q2;
                }
                if ($year == 3) {
                    $year3q3 = true;
                    //   echo "year3q2".$year3q2;
                }
            }
            if ($month == 10 || $month == 11 || $month == 12) {
                if ($year == 1) {
                    $year1q4 = true;
                    // echo "year1q3".$year1q3;

                }
                if ($year == 2) {
                    $year2q4 = true;
                    // echo "year2q3".$year2q3;
                }
                if ($year == 3) {
                    $year3q4 = true;
                    //echo "year3q3".$year3q3;
                }
            }
            if ($month == 1 || $month == 2 || $month == 3) {
                if ($year == 1) {
                    $year1q1 = true;
                    //echo "year1q4".$year1q4;
                }
                if ($year == 2) {
                    $year2q1 = true;
                    // echo "year2q4".$year2q4;
                }
                if ($year == 3) {
                    $year3q1 = true;
                    // echo "year3q4".$year3q4;
                }
            }

            $formuladata = \DB::select(\DB::raw("select kpi_def.name,kpi_def.value_explanation,kpi_performance_type.formula,kpi_performance_type.factor_1,kpi_performance_type.factor_2 from kpi_def join kpi_performance_type on kpi_def.value_explanation=kpi_performance_type.id where kpi_def.id=$kpi_id"));

            $maintenanceObject[$i]['name'] = $formuladata[0]->name;

            \DB::select(\DB::raw("set @period_q := 3"));
            \DB::select(\DB::raw("set @period_h:= 6"));
            \DB::select(\DB::raw("set @period_y:= 12"));
            \DB::select(\DB::raw("set @kpi_target_id := $kpi_target_id"));

            $valuestadata = \DB::select(\DB::raw("SELECT kvs.year_no,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_q ) q_max_value,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_h ) h_max_value,

	f_get_kpi_base ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_base_value,
	f_get_kpi_min ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_min_value,
	f_get_kpi_max ( kvs.kpi_target_id, kvs.year_no, @period_y ) y_max_value,

	kvs.q1_value,
	kvs.q1_target,
	kvs.q2_value,
	kvs.q2_target,
	kvs.q3_value,
	kvs.q3_target,
	kvs.q4_value,
	kvs.q4_target,

	kvs.h1_value,
	kvs.h1_target,
	kvs.h2_value,
	kvs.h2_target,

	kvs.y_value,
	kvs.y_target

FROM kpi_values_stats kvs WHERE
	kvs.kpi_target_id = $kpi_target_id AND
    kvs.year_no = $target_year
ORDER BY kvs.year_no;"));
            // var_dump($valuestadata);


            foreach ($valuestadata as $valdata) {

                $year_no = $valdata->year_no;
                $q1value = $valdata->q1_value;
                $q2value = $valdata->q2_value;
                $q3value = $valdata->q3_value;
                $q4value = $valdata->q4_value;
                $h1value = $valdata->h1_value;
                $h2value = $valdata->h2_value;
                $yvalue = $valdata->y_value;

                $q1target = $valdata->q1_target;
                $q2target = $valdata->q2_target;
                $q3target = $valdata->q3_target;
                $q4target = $valdata->q4_target;
                $h1target = $valdata->h1_target;
                $h2target = $valdata->h2_target;
                $ytarget = $valdata->y_target;
                $formula = $formuladata[0]->formula;
                $qmin_value = $valdata->q_min_value;
                $qmax_value = $valdata->q_max_value;
                $qbase_value = $valdata->q_base_value;
                $hmin_value = $valdata->h_min_value;
                $hmax_value = $valdata->h_max_value;
                $hbase_value = $valdata->h_base_value;
                $ymin_value = $valdata->y_min_value;
                $ymax_value = $valdata->y_max_value;
                $ybase_value = $valdata->y_base_value;

                $factor_1 = $formuladata[0]->factor_1;
                $factor_2 = $formuladata[0]->factor_2;

                //  echo "the calculation is";

                $y_perf = $this->perfcalculate($yvalue, $formula, $ymin_value, $ymax_value, $ybase_value, $factor_1,
                    $factor_2, $ytarget);
                //  echo "yperf=".$y_perf."<br/>";
                $y_prog = $this->progfcalculate($yvalue, $ybase_value, $ytarget);
                // echo "$y_prog=".$y_prog."<br/>";

                $maintenanceObject[$i]['y_perf'] = (round($y_perf, 8));
                $maintenanceObject[$i]['y_prog'] = (round($y_prog, 8));
                if ($year1q1 == true || $year2q1 == true || $year3q1 == true) {

                    $q1_perf = $this->perfcalculate($q1value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q1target);
                    $q1_prog = $this->progfcalculate($q1value, $qbase_value, $q1target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);
                    $maintenanceObject[$i]['q1_perf'] = (round($q1_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q1_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h1_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h1_prog, 8));

                    $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q1_perf='$q1_perf',q1_prog='$q1_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_target_id and year_no=$year_no"));
                    /*if ($year1q1 == true && $year_no == 1) {
                        $maintenanceObject[$i]['y_perf'] = $y_perf;
                        $maintenanceObject[$i]['y_prog'] = $y_prog;
                    }
                    if ($year2q1 == true && $year_no == 2) {

                    }
                    if ($year3q1 == true && $year_no == 3) {

                    }*/

                }
                if ($year1q2 == true || $year2q2 == true || $year3q2 == true) {
                    $q2_perf = $this->perfcalculate($q2value, $formula, $qmin_value, $qmax_value, $qbase_value,
                        $factor_1, $factor_2, $q2target);
                    $q2_prog = $this->progfcalculate($q2value, $qbase_value, $q2target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);

                    $maintenanceObject[$i]['q1_perf'] = (round($q2_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q2_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h1_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h1_prog, 8));

                    $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q2_perf='$q2_perf',q2_prog='$q2_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_target_id and year_no=$year_no"));

                }
                if ($year1q3 == true || $year2q3 == true || $year3q3 == true) {
                    $q3_perf = $this->perfcalculate($q3value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q3target);
                    $q3_prog = $this->progfcalculate($q3value, $qbase_value, $q3target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);
                    $maintenanceObject[$i]['q1_perf'] = (round($q3_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q3_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h2_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h2_prog, 8));

                    $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q3_perf='$q3_perf',q3_prog='$q3_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_target_id and year_no=$year_no"));

                }
                if ($year1q4 == true || $year2q4 == true || $year3q4 == true) {

                    $q4_perf = $this->perfcalculate($q4value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q4target);
                    $q4_prog = $this->progfcalculate($q4value, $qbase_value, $q4target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);

                    $maintenanceObject[$i]['q1_perf'] = (round($q4_perf, 8));
                    $maintenanceObject[$i]['q1_prog'] = (round($q4_prog, 8));
                    $maintenanceObject[$i]['h1_perf'] = (round($h2_perf, 8));
                    $maintenanceObject[$i]['h1_prog'] = (round($h2_prog, 8));

                    $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q4_perf='$q4_perf',q4_prog='$q4_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_target_id and year_no=$year_no"));
                }
            }
            //}
            $i++;
        }
        return response()->json([
            "code" => 200,
            "msg" => "data updated successfully"
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('clientapp::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(ObjectModelStore $request)
    {
    }


    public function edit()
    {
        return view('clientapp::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Response
     */
    public function update(ObjectModelUpdate $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
    }

    public function show($id)
    {
    }
}

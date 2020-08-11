<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class GaugeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $mtp_id = $request->mtp_date;
        $kpi_id = $request->kpi_id;
        $periodicity = $request->periodicity;
        \DB::select(\DB::raw("set @month_count := $periodicity"));
            $userData1 = \DB::select(\DB::raw("select f_get_kpi_base(kvs.kpi_target_id, kvs.year_no, $periodicity) base_value, f_get_kpi_min(kvs.kpi_target_id, kvs.year_no, $periodicity) min_value, f_get_kpi_max(kvs.kpi_target_id, kvs.year_no, $periodicity) max_value,mtp.`name` mtp_name, fys.start_date mtp_start_date, fye.end_date mtp_end_name, sector.`name` sector_name, sub.`name` unit_name, kpi.`name` kpi_name, CURDATE() data_as_of_date,kpi.value_unit, ku.`name` value_unit_name, ku.short_name value_unit_short_name, kpi.value_type, kpi.numerator_name, kpi.denominator_name, kpi.value_explanation, kpi.rounding_decimals,
pt.formula performance_formula, pt.name performance_name  , pt.factor_1 perf_factor_1, pt.factor_2 perf_factor_2,
/**min: set to 0, max: set to target_value*1.2, base: set to target_value*0.95 **/
        case @month_count
        when 3 then
                case floor (round((2+kval_last.target_month) / 3, 1)) -- round to avoid possible pc arithmatic tiny fractions, +2 to adjust for getting 1,2,3,4 based on the 12months range
                        when 1 then kvs.q1_value
                        when 2 then kvs.q2_value
                        when 3 then kvs.q3_value
                        when 4 then kvs.q4_value
                end
        when 6 then
            case floor (round((5+kval_last.target_month) / 6, 1))
                        when 1 then kvs.h1_value
                        when 2 then kvs.h2_value
            end
        when 12 then kvs.y_value
        when 36 then
                if(kpi.value_type=1,
                (select sum(kvsi.y_value) from kpi_values_stats kvsi where kvsi.kpi_target_id = kvs.kpi_target_id and kvsi.year_no between 1 and 3/*kvs.year_no*/),
                kvs.y_value /*value of the current year*/)
        end as acc_value,

        target.margin_pct,
        case @month_count
        when 3 then
                case floor (round((2+kval_last.target_month) / 3, 1))
                        when 1 then kvs.q1_target
                        when 2 then kvs.q2_target
                        when 3 then kvs.q3_target
                        when 4 then kvs.q4_target
                end
        when 6 then
                case floor (round((5+kval_last.target_month) / 6, 1))
                        when 1 then kvs.h1_target
                        when 2 then kvs.h2_target
                end
        when 12 then kvs.y_target
        when 36 then
            if(kpi.value_type=1,
                (select sum(kvsi.y_target) from kpi_values_stats kvsi where kvsi.kpi_target_id = kvs.kpi_target_id and kvsi.year_no between 1 and 3/*kvs.year_no*/),
                (select kvsi.y_target from kpi_values_stats kvsi where kvsi.kpi_target_id = kvs.kpi_target_id and kvsi.year_no = 3 /*target of the 3rd year*/)
                )
        end as target_value,
        /*target.min_allowed_value_p min_allowed_value, -> replaced by: (target_value * (1 - margin_pct)) */
        /*target.improved_value_p improved_value, -> replaced by: (target_value * (1 + margin_pct))*/

        kval_last.target_date as last_value_date,
        kval_last.actual_date as last_value_date_actual,
        kval_last.actual_value as last_value,
        ( select min(kval_n.target_date) from kpi_values kval_n /*n: next*/ where
                kval_n.kpi_target_id = target.id and
                kval_n.target_date > kval_last.target_date
        ) as next_value_date,

        (select max(kval_d.target_date) from kpi_values kval_d /*d: due*/ where
                kval_d.kpi_target_id = target.id and
                kval_d.target_date < CURDATE()
        ) as due_value_date,

        (
        select kval_py.actual_value from kpi_values kval_py, kpi_target t_py /*py: prev year*/ where
            kval_py.kpi_target_id = t_py.id and /*could be a previous target other than current one*/
          kval_py.target_year = case when kval_last.target_year > 1 then kval_last.target_year - 1 else 3 end and
            kval_py.target_month = kval_last.target_month and
            t_py.id = (case when kval_last.target_year > 1 then target.id else
                    (select t_pyi.id from kpi_target t_pyi, mtp where
                            mtp.id = target.mtp_id and
                            t_pyi.kpi_id = target.kpi_id and
                            t_pyi.mtp_id = mtp.prev_mtp and
                            t_pyi.value_periodicity = target.value_periodicity
                    )	end
            )
        ) as prev_year_value,
        /*kpi_status (or kpi index): replaced by: (acc_value - min_allowed_value) / (target_value - min_allowed_value) */
        kpi.value_type kpi_value_type, /*1: number, 2: percentage, 3: ratio 4: rate*/
        target.id kpi_target_id
        from kpi_def kpi, subtenant sector, subtenant sub, mtp, fiscal_year fys, fiscal_year fye, kpi_unit ku, kpi_performance_type pt, kpi_values_stats kvs,
				kpi_target target LEFT JOIN kpi_values kval_last
        ON target.id = kval_last.kpi_target_id and /*the records must exist in kpi_values, even with null values*/
                    kval_last.target_date = ( /*last actual date (having not null actual value)*/
                        select max(kval2.target_date) from kpi_values kval2 where
                            kval2.kpi_target_id = target.id and
                            kval2.actual_value is not null and
                            kval2.actual_date <= CURDATE()
                    )
        where
        kpi.child_subtenant_id = sub.id and
        kpi.subtenant_id = sector.id and
        target.kpi_id = kpi.id and
        target.mtp_id = mtp.id and
        fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
				ku.id = kpi.value_unit and
				pt.id = kpi.value_explanation and
        kvs.kpi_target_id = target.id and
        kvs.year_no = ifnull(kval_last.target_year,1)	and
        kpi.id = $kpi_id /**argument**/ and
        mtp.id = $mtp_id /**argument**/"));

        //    $target_id = $userData1[0]->kpi_target_id; // your input

        //echo($target_id);

        //$this->quarterMap($data);
        //Session(['target_id'=>$target_id]);
        // $this->quarterMap($target_id);
        //return view('clientapp::index');
        if ($userData1) {
            $value = $userData1[0]->acc_value;
            $target     = $userData1[0]->target_value;
            $formula    = $userData1[0]->performance_formula;
            $mn        = ($userData1[0]->min_value) ? $userData1[0]->min_value : 0;
            $mx        = ($userData1[0]->max_value) ? $userData1[0]->max_value : $target*1.2;
            $base       = $userData1[0]->base_value;
            $factor_1   = $userData1[0]->perf_factor_1;
            $factor_2   = $userData1[0]->perf_factor_2;

            if($formula) {
                $result = eval("return " . $formula . ";");
            }
            if($userData1[0]->value_type==2) {
                $mx = $mx*100;
            }
            $interval = (($mx-$mn)/10);
            return response()->json([
                "code" => 200,
                "data" => $userData1,
                "performance" => $result,
                "min" => $mn,
                "max" => $mx,
                "interval" => $interval//(($mn-$mx)/10)
            ]);
        } else {
            return response()->json([
                "code" => 200,
                "data" => []
            ]);
        }
        //echo $request;


    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function quarterMap(Request $request)
    {

        $target_id = $request->target_id;
        //echo($target_id);
        if($target_id!=0)
        {
            $userData1 = \DB::select(\DB::raw(" select f_get_kpi_base(kvs.kpi_target_id, kvs.year_no, $request->periodicity) base_value, f_get_kpi_min(kvs.kpi_target_id, kvs.year_no, $request->periodicity) min_value, f_get_kpi_max(kvs.kpi_target_id, kvs.year_no, $request->periodicity) max_value, kvs.year_no, kvs.q1_value, kvs.q1_target, kvs.q2_value, kvs.q2_target, kvs.q3_value, kvs.q3_target, kvs.q4_value, kvs.q4_target,
        kvs.h1_value, kvs.h1_target, kvs.h2_value, kvs.h2_target,
        kvs.y_value, kvs.y_target
from kpi_values_stats kvs where
kvs.kpi_target_id = $target_id  and
kvs.year_no > 0
order by kvs.year_no;"));
            if ($userData1) {

                $dataChecl = (json_decode(json_encode($userData1), true));
                $valuessss = [];
                $valLabel = [];
                $j=0;


                foreach ($dataChecl as $key => $value) {
                    if($request->periodicity == 3) {
                        for($i=1; $i <=4 ; $i++) {
                            if(isset($value['q'.$i.'_value']) && $value['q'.$i.'_value'] != null) {
                                $valuessss[$j] = ($request->valType ==2) ? $value['q'.$i.'_value']*100 : $value['q'.$i.'_value'];
                                $valLabel[$j] = 'q'.$i.'_value';
                                $j++;
                            }
                        }
                    } else if($request->periodicity == 6) {
                        for($i=1; $i <=2 ; $i++) {
                            if(isset($value['h'.$i.'_value']) && $value['h'.$i.'_value'] != null) {
                                $valuessss[$j] = ($request->valType ==2) ? $value['h'.$i.'_value']*100 : $value['h'.$i.'_value'];
                                $valLabel[$j] = 'h'.$i.'_value';
                                $j++;
                            }
                        }
                    } else if($request->periodicity == 12 || $request->periodicity == 36) {
                        for($i=1; $i <=1 ; $i++) {
                            if(isset($value['y_value']) && $value['y_value'] != null) {
                                $valuessss[$j] = ($request->valType ==2) ? $value['y_value']*100 : $value['y_value'];
                                $valLabel[$j] = 'y_value';
                                $j++;
                            }
                        }
                    }
                }

                array_unshift($valuessss, 1);
                array_unshift($valLabel, 'start');

                //var_dump($valuessss);
                return response()->json([
                    "code" => 200,
                    "data" => $userData1,
                    "arrayVal" => $valuessss,
                    "arraylabel" => $valLabel,
                ]);
            }
        }
        else {
            return response()->json([
                "code" => 200,
                "data" => []
            ]);
        }


    }

    public function mtp()
    {
        $userData1 = \DB::select(\DB::raw(" select id,name from mtp ;"));
        return response()->json([
            "code" => 200,
            "data" => $userData1
        ]);


    }

    public function loadkpi($id)
    {
        $kpilist = \DB::select(\DB::raw("select kpi_def.*,tenant.name as tenantname,subtenant.name as subtenantname,process_def.name as processname from kpi_def inner join tenant on kpi_def.tenant_id=tenant.id inner join subtenant on kpi_def.subtenant_id=subtenant.id inner join process_def on kpi_def.scope_id=process_def.id where kpi_def.child_subtenant_id=$id"));
        return response()->json([
            "code" => 200,
            "data" => $kpilist
        ]);


    }
    public function loadkpisymbol($id)

    {
        $kpisymbol = \DB::select(\DB::raw("select kpi_def.*,tenant.name as tenantname,subtenant.name as subtenantname,process_def.name as processname from kpi_def inner join tenant on kpi_def.tenant_id=tenant.id inner join subtenant on kpi_def.subtenant_id=subtenant.id inner join process_def on kpi_def.scope_id=process_def.id where kpi_def.child_subtenant_id=$id"));
        return response()->json([
            "code" => 200,
            "data" => $kpisymbol
        ]);


    }


}

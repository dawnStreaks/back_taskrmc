<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class DashboardKPIController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function staticdashboardTableVal(Request $request)
    {
        $mtp_id = $request->mtp_date;
        $sector_id = $request->sector;
        $getAll = $request->getAll;

        if ($mtp_id == 'null') {
            $mtp_id = 4;
        }
        if ($sector_id == 'null') {
            $sector_id = 2;
        }

        if ($getAll == 'undefined' || $getAll == 'false') {
            $getAll = false;
        }

        $dash_val = \DB::select(\DB::raw("call p_unit_perf_prog_recursive(
				$sector_id, -- a_subtenant_id: argument: (the parent subtenant id)
				/**
					-- Description: same as used with the old query:
					-- 1) if nothing is selected in the filter (i.e. null sector, null org unit)
									-> put the argument = 2 (it's the parent id of the sectors)
					-- 2) if sector in the filter is selected, and no org unit
									-> put the argument = id of the sector
					-- 3) if sector in the filter is selected, and org unit is selected
									-> put the argument = id of the org unit
				**/
				$mtp_id, -- a_mtp_id: argument
				'Y', -- fixed
				1 -- fixed
				);"));

        $mainVals = [];

        $activeCount = $notActiveCount = $uptodateCount = $notUpdatetodate = 0;
        $efficiencyCount = $strategyCount = $highCount = $midCount = $lowCount = 0;
	$activeCountSector= $activeCountStrategy = 0;
	$activeSectorName = '';
        $j = 0;
        foreach ($dash_val as $vals) {
//            if ($vals->sub_id != 2) {
//echo $vals->kpi_count;
            if (!$getAll && $vals->kpi_count != '') {
                if ($j == 0) {
                    $activeCount = $activeCount + $vals->kpi_active;
                    $notActiveCount = $notActiveCount + ($vals->kpi_count - $vals->kpi_active);
                    $uptodateCount = $uptodateCount + $vals->kpi_up_to_date;
                    $notUpdatetodate = $notUpdatetodate + ($vals->kpi_count - $vals->kpi_up_to_date);
                    $efficiencyCount = $efficiencyCount + $vals->kpi_eff;
                    $strategyCount = $strategyCount + ($vals->kpi_count - $vals->kpi_eff);
                    $highCount = $highCount + $vals->kpi_importance_h;
                    $midCount = $midCount + $vals->kpi_importance_m;
                    $lowCount = $lowCount + $vals->kpi_importance_l;
                }


		if($sector_id != 2 && $sector_id == $vals->sub_parent) {
		    $activeCountStrategy = $activeCountStrategy + $vals->kpi_count;
                    $activeCountSector = $activeCountSector +  ($vals->kpi_count - $vals->kpi_eff);
		    $activeSectorName = $vals->sector_name;
                }

                $vals->by_strategy = ($vals->kpi_count - $vals->kpi_eff);
                $vals->not_uptodate = ($vals->kpi_count - $vals->kpi_up_to_date);
                $vals->not_active = ($vals->kpi_count - $vals->kpi_active);
                $mainVals[] = $vals;
                $j++;
            }
            if ($getAll) {
                if ($j == 0) {
                    $activeCount = $activeCount + $vals->kpi_active;
                    $notActiveCount = $notActiveCount + ($vals->kpi_count - $vals->kpi_active);
                    $uptodateCount = $uptodateCount + $vals->kpi_up_to_date;
                    $notUpdatetodate = $notUpdatetodate + ($vals->kpi_count - $vals->kpi_up_to_date);
                    $efficiencyCount = $efficiencyCount + $vals->kpi_eff;
                    $strategyCount = $strategyCount + ($vals->kpi_count - $vals->kpi_eff);
                    $highCount = $highCount + $vals->kpi_importance_h;
                    $midCount = $midCount + $vals->kpi_importance_m;
                    $lowCount = $lowCount + $vals->kpi_importance_l;
                }

		if($sector_id != 2 && $sector_id == $vals->sub_parent) {
		    $activeCountStrategy = $activeCountStrategy + $vals->kpi_count;
                    $activeCountSector = $activeCountSector +  ($vals->kpi_count - $vals->kpi_eff);
		    $activeSectorName = $vals->sector_name;
                }
                $vals->by_strategy = ($vals->kpi_count - $vals->kpi_eff);
                $vals->not_uptodate = ($vals->kpi_count - $vals->kpi_up_to_date);
                $vals->not_active = ($vals->kpi_count - $vals->kpi_active);
                $mainVals[] = $vals;
                $j++;
            }
            //          }
            //var_dump($vals->sub_id);
        }
        $counts['activeCount'] = $activeCount;
        $counts['notActiveCount'] = $notActiveCount;
        $counts['uptodateCount'] = $uptodateCount;
        $counts['notUpdatetodate'] = $notUpdatetodate;
        $counts['efficiencyCount'] = $efficiencyCount;
        $counts['strategyCount'] = $strategyCount;
        $counts['highCount'] = $highCount;
        $counts['midCount'] = $midCount;
        $counts['lowCount'] = $lowCount;
	$counts['activeCountSector'] = $activeCountSector;
	$counts['activeSectorName'] = $activeSectorName;
	$counts['activeCountStrategy'] = $activeCountStrategy;

        return response()->json([
            "code" => 200,
            "data" => $mainVals,
            "counts" => $counts
        ]);

    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function dashboardTableVal(Request $request)
    {
        $mtp_id = $request->mtp_date;
        $sector_id = $request->sector;
        // $org_unit_id = $request->org_unit;

        $p = '="process_def"';
        $o = '="objective"';
        $dash_val = \DB::select(\DB::raw(" WITH RECURSIVE cte (l1_id, id, parent_id, subtenant_type) AS (
            -- This is end of the recursion: Select top level
            select id, id, parent_id, subtenant_type_id
                from subtenant where
                parent_id = $sector_id -- set your arg here
            UNION ALL
            -- This is the recursive part: It joins to cte
            select c.l1_id, s.id, s.parent_id, s.subtenant_type_id
                from subtenant s
                inner join cte c on s.parent_id = c.id
            )
            select sub_ch.id as sub_id, sub_ch.name as sub_name, count(1) as kpi_count,
                sum(kd.active_status) as active_count,
                sum(case when (not exists (select 1 from kpi_values kv where kv.kpi_target_id = kt.id and kv.actual_value is null and kv.target_date <= SYSDATE())
                                and exists(select 1 from kpi_values kv where kv.kpi_target_id = kt.id) ) then 1 else 0 end) as up_to_date_count,
                sum(case when kd.scope_table $p then 1 else 0 end) as process_count,
                sum(case when kd.scope_table $o then 1 else 0 end) as strategy_count -- ,
                -- sum(case when kd.importance = 3 then 1 else 0 end) as imp_hi_count,
                -- sum(case when kd.importance = 2 then 1 else 0 end) as imp_mid_count,
                -- sum(case when kd.importance = 1 then 1 else 0 end) as imp_low_count
            from cte, kpi_def kd, kpi_target kt, subtenant sub_ch where
                    sub_ch.id = cte.l1_id and
                    cte.subtenant_type = 6 and -- set your arg here / or comment that line in case you want for all the subtenant types
                    cte.id = kd.child_subtenant_id and
                    kt.kpi_id = kd.id and
                    kt.mtp_id = $mtp_id -- set your arg here
            group by sub_id, sub_name

            ;"));
        return response()->json([
            "code" => 200,
            "data" => $dash_val
        ]);

    }

    public function dashboardVal(Request $request)
    {
        $mtp_id = $request->mtp_date;
        $sector_id = $request->sector;
        $org_unit_id = $request->org_unit;

        $p = '="process_def"';
        $o = '="objective"';
        $dash_val = \DB::select(\DB::raw(" WITH RECURSIVE cte (id, parent_id, subtenant_type) AS (
            -- This is end of the recursion: Select top level
            select id, parent_id, subtenant_type_id
                from subtenant where
                id = $sector_id -- set your arg here
            UNION ALL
            -- This is the recursive part: It joins to cte
            select s.id, s.parent_id, s.subtenant_type_id
                from subtenant s
                inner join cte c on s.parent_id = c.id
            )
            select count(1) as kpi_count,
                sum(kd.active_status) as active_count,
                sum(case when (not exists (select 1 from kpi_values kv where kv.kpi_target_id = kt.id and kv.actual_value is null and kv.target_date <= SYSDATE())
                                and exists(select 1 from kpi_values kv where kv.kpi_target_id = kt.id) ) then 1 else 0 end) as up_to_date_count,
                sum(case when kd.scope_table $p then 1 else 0 end) as process_count,
                sum(case when kd.scope_table $o then 1 else 0 end) as strategy_count,
                sum(case when kd.importance = 3 then 1 else 0 end) as imp_hi_count,
                sum(case when kd.importance = 2 then 1 else 0 end) as imp_mid_count,
                sum(case when kd.importance = 1 then 1 else 0 end) as imp_low_count
            from cte, kpi_def kd, kpi_target kt where
                    cte.subtenant_type = 6 and -- set your arg here / or comment that line in case you want for all the subtenant types
                    cte.id = kd.child_subtenant_id and
                    kt.kpi_id = kd.id and
                    kt.mtp_id = $mtp_id -- set your arg here
            ;"));
        return response()->json([
            "code" => 200,
            "data" => $dash_val
        ]);

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */


    public function mtp()
    {
        $userData1 = \DB::select(\DB::raw(" select id,name from mtp ;"));
        return response()->json([
            "code" => 200,
            "data" => $userData1
        ]);


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

    public function loadSubTenants($id)
    {

        $subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)) from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '-'), '>', s.name), s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id) select id, name from cte order by path"));
        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $subtenants
            ]);
        }
    }


}

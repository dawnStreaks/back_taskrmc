<?php

namespace Modules\ClientApp\Reports;
class KpiStatusReport extends \koolreport\KoolReport
{
    use \koolreport\laravel\Friendship;
    // By adding above statement, you have claim the friendship between two frameworks
    // As a result, this report will be able to accessed all databases of Laravel
    // There are no need to define the settings() function anymore
    // while you can do so if you have other datasources rather than those
    // defined in Laravel.

    use \koolreport\inputs\Bindable;
    use \koolreport\inputs\POSTBinding;

    public $sect;
     function __construct(array $params = array())
     {
         $this->sect = $this->params['sect'];
         $this->org = $this->params['org'];

         parent::__construct($params);
     }

    protected function defaultParamValues()
    {
        return array(
            "sector" => "",
            "section" => "",
            "mtp" => 4,
            "periodicity" => 3,
            "kpi_category" => 0,
            "kpi_activation_status" => -1,
            "kpi_status" => -1,
            "debug_modeprog"=>false,
            "debug_modeperf"=>true,
            // "backlink"=>0,
            // "backlink2"=>
        );
    }

    protected function bindParamsToInputs()
    {
        return array(
            "sector",
            "section",
            "mtp",
            "periodicity",
            "kpi_category",
            "kpi_activation_status",
            "kpi_status",
            "debug_modeprog",
            "debug_modeperf",
            "backlink",

            
        );
    }

    public function settings()
    {
        return array(
            "dataSources" => array(
                "mysql" => array(
                    'host' => env('DB_HOST'),
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                    'dbname' => env('DB_DATABASE'),
                    'charset' => 'utf8',
                    'class' => "\koolreport\datasources\MySQLDataSource",
                ),
            )
        );
    }

    function setup()
    {
        // Let say, you have "sale_database" is defined in Laravel's database settings.
        // Now you can use that database without any futher setitngs.
       
        // $sect = '';
        if (isset($this->params['sect']) && !empty($this->params['sect']) && empty($_POST['sector']) &&  $this->params['sect']!="null" && $this->params['sect']!="undefined") {
           
            // var_dump($this->params["org"]);

            $this->params['sector']= $this->params['sect'];
            $this->params['sect']="null";
            // $this->params["backlink"]=1;
            // var_dump($this->params["sector"]);
            // var_dump($this->params["sect"]);

        }

        if (isset($this->params['org']) && !empty($this->params['org']) && empty($_POST['section']) &&  $this->params['org']!="null" && $this->params['org']!="undefined") {
           
            $this->params['section']= $this->params['org'];
            $this->params['org']="null";
            // var_dump($this->params["section"]);
            // var_dump($this->params["org"]);

        }


        if (isset($this->params['kpi_category']) && empty($this->params['kpi_category'])) {
            $this->params["kpi_category"] = 0;
        }
        if (isset($this->params['kpi_activation_status']) && $this->params['kpi_activation_status'] == "") {
            $this->params["kpi_activation_status"] = -1;
        }
        if (isset($this->params['kpi_status']) && $this->params['kpi_status'] == "") {
            $this->params["kpi_status"] = -1;
        }
        if (isset($this->params['sector']) && !empty($this->params['sector'])) {
            $parent_id = $this->params["sector"];
            if (isset($this->params['section']) && !empty($this->params['section'])) {
                $parent_id = $this->params["section"];
            } else {
                $parent_id = $this->params["sector"];
            }
        } else {
            $parent_id = 2;

        }
        $this->src("mysql")
            // ->query("set :kpi_category_id = :kpi_category_id;")
            ->query("WITH RECURSIVE cte (id, name, parent_id, level, path, subtenant_type) AS (
                /**This is end of the recursion: Select top level**/
                select 	id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)), subtenant_type_id from subtenant where
                                        id = $parent_id /**set your arg here**/
                UNION ALL
                /**This is the recursive part: It joins to cte**/
                select 	s.id, s.name, s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id), s.subtenant_type_id from subtenant s
                                        inner join cte c on s.parent_id = c.id
            )
            select 	cte.id sub_id, cte.name sub_name, kd.id kpi_id, kd.symbol kpi_symbol, kd.name kpi_name, 
                                    kd.kpi_cat, kd.active_status, kd.status,
                                    kd.value_type, kd.numerator_name, kd.denominator_name, /**make the formula from numerator/denominator for non number value_type**/
                                    case :periodicity_id 
                                    when 3 then
                                                    case floor (round((2+kval_last.target_month) / 3, 1)) /**round to avoid possible pc arithmatic tiny fractions, +2 to adjust for getting 1,2,3,4 based on the 12months range**/
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
                                                    if(kd.value_type=1,
                                                    (select sum(kvsi.y_value) from kpi_values_stats kvsi where kvsi.kpi_target_id = kvs.kpi_target_id and kvsi.year_no between 1 and 3/**kvs.year_no**/),
                                                    kvs.y_value /**value of the current year**/)
                                    end as acc_value,		
                        case :periodicity_id
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
                                if(kd.value_type=1,
                                    (select sum(kvsi.y_target) from kpi_values_stats kvsi where kvsi.kpi_target_id = kvs.kpi_target_id and kvsi.year_no between 1 and 3/**kvs.year_no**/),
                                    (select kvsi.y_target from kpi_values_stats kvsi where kvsi.kpi_target_id = kvs.kpi_target_id and kvsi.year_no = 3 /**target of the 3rd year**/)
                                    )
                        end as target_value,	
                                    pt.formula performance_formula, pt.factor_1 perf_factor_1, pt.factor_2 perf_factor_2,
                                    f_get_kpi_min (kt.id, kval_last.target_year, :periodicity_id) min_value,
                                    f_get_kpi_max (kt.id, kval_last.target_year, :periodicity_id) max_value,
                                    f_get_kpi_base (kt.id, kval_last.target_year, :periodicity_id) base_value,
                                    
                                    (
                                            select min(ikval.target_date) from kpi_values ikval where
                                                            ikval.kpi_target_id = kt.id and
                                                            ikval.actual_value is null
                                    ) next_reading_date,
                                    u_comm.id u_comm_id,
                                    u_comm.name u_comm_name,
                                    u_coord.id u_coord_id,
                                    u_coord.name u_coord_name,
                                    kd.scope_table,
                                    kd.importance,
                                    kt.margin_pct,
                                    kd.value_explanation v_exp_id,
                                    pt.name v_exp_name,
                                    ku.id unit_id,
                                    ku.name unit_name
            from cte, kpi_def kd
                            , kpi_performance_type pt
                            , users u_comm
                            , users u_coord
                            , kpi_unit ku
                            , kpi_target kt LEFT JOIN kpi_values kval_last 
                            ON kt.id = kval_last.kpi_target_id and 
                                            kval_last.target_date = ( 
                                                    select max(kval2.target_date) from kpi_values kval2 where
                                                            kval2.kpi_target_id = kt.id and
                                                            kval2.actual_value is not null and
                                                            kval2.target_date <= CURDATE()
                                            )	
                            left outer join kpi_values_stats kvs on
                                                    kvs.kpi_target_id = kt.id and
                                                    kvs.year_no = ifnull(kval_last.target_year,1)
            where 
                            cte.subtenant_type = 6 and 
                            cte.id = kd.child_subtenant_id and
                            pt.id = kd.value_explanation and
                            u_comm.id = kd.user_of_contact and
                            u_coord.id = kd.user_of_coordination and
                            ku.id = kd.value_unit and
                            kt.kpi_id = kd.id and
                            kt.mtp_id = :mtp_id  /**set your arg here**/
                            and (kd.kpi_cat =:kpi_category_id or :kpi_category_id=0  )
                            and (kd.active_status =:kpi_activation_status_id or :kpi_activation_status_id<0)
                            and (kd.status =1) 
            order by path, kd.symbol;")
            ->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"], ":periodicity_id" => $this->params["periodicity"], ":kpi_category_id" => $this->params["kpi_category"], ":kpi_activation_status_id" => $this->params["kpi_activation_status"], ":kpi_status_id" => $this->params["kpi_status"]))
            ->pipe($this->dataStore('user_details'));

        $this->src("mysql")
            ->query("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3")
            ->pipe($this->dataStore('sector1'));

        if ($this->params["sector"] != null) {
            $this->src("mysql")
                ->query("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '_') from subtenant where parent_id = :sector_id UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('_', 50), :sector_id, '', CONCAT(id, '') from subtenant where parent_id = :sector_id) select id, name from cte order by path")
                ->params(array(":sector_id" => $this->params["sector"]))
                ->pipe($this->dataStore('section1'));
        }
        $this->src("mysql")
            ->query("select id,name from mtp")
            ->pipe($this->dataStore('mtp1'));
        $this->src("mysql")
            ->query("select id, name from kpi_cat")
            ->pipe($this->dataStore('category1'));

        $this->src("mysql")
            ->query("select * from trans_table")
            ->pipe($this->dataStore('translation'))->requestDataSending();
        $this->src("mysql")
            ->query("select id,name from subtenant where id=$parent_id")
            ->pipe($this->dataStore('sector_name'))->requestDataSending();
    }
}

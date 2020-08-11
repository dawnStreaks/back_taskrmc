<?php

namespace Modules\ClientApp\Reports;
use \koolreport\pivot\processes\Pivot;
use \koolreport\processes\Filter;
use \koolreport\processes\ColumnMeta;
use \koolreport\processes\CalculatedColumn;

class KpiExceptionReport extends \koolreport\KoolReport
{
    use \koolreport\clients\jQuery;
      use \koolreport\clients\Bootstrap;
//  use \koolreport\clients\FontAwesome;
    //  use \koolreport\laravel\Friendship;
     


    // By adding above statement, you have claim the friendship between two frameworks
    // As a result, this report will be able to accessed all databases of Laravel
    // There are no need to define the settings() function anymore
    // while you can do so if you have other datasources rather than those
    // defined in Laravel.

    use \koolreport\inputs\Bindable;
    use \koolreport\inputs\POSTBinding;
    
    
   

    protected function defaultParamValues()
    {
        return array(
            "sector" => "",
            "section" => "",
            "mtp" => 4,
            "expand"=>"",
          //  "filter"=>1,
            "expand1"=>""
                    );
    }

    protected function bindParamsToInputs()
    {
        return array(
            "sector",
            "section",
            "mtp",
            "expand",
            // "filter",
            "expand1"
        );
    }

    public function settings()
    {
        return array(
            // 'assets' => array(
            //     'path' => '../../../public',
            //     'url' => 'public/',
            // ),
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
        if (isset($this->params['sector']) && !empty($this->params['sector'])) {
            $parent_id = $this->params["sector"];
            if (isset($this->params['section']) && !empty($this->params['section'])) {
                $parent_id = $this->params["section"];
            } else {
                $parent_id = $this->params["sector"];
            }
        } else {
            $parent_id = "null";

        }
        $tenant_id = 1;
            $parent_sub_id = $parent_id;
            $mtp_id=$this->params["mtp"];
            
        $date2=date("Y-m-d");
        // var_dump($_POST);
        $this->src("mysql")
            // ->query("set :kpi_category_id = :kpi_category_id;")
            ->query("
            WITH RECURSIVE cte (id, name, parent_id, level, path, subtenant_type) AS (
                /**This is end of the recursion: Select top level**/
                select 	id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)), subtenant_type_id from subtenant where
                                        (
                                                ($parent_sub_id is not null and id = $parent_sub_id)
                                                or
                                                ($parent_sub_id is null and parent_id is null and tenant_id = $tenant_id)
                                        )		
                UNION ALL
                /**This is the recursive part: It joins to cte**/
                select 	s.id, s.name, s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id), s.subtenant_type_id from subtenant s
                                        inner join cte c on s.parent_id = c.id
            )
            select cte.id as org_unit_id, cte.`name` as org_unit_name, kd.id as kpi_id, kd.`name` as kpi_name
                                    , kd.active_status, u_co.id as user_coord_id, u_co.`name` as user_coord_name,u_co.last_name as user_coord_lname
                                    , ket.id as exception_type_id, ke.id as exception_id
                                    , ket.short_desc_ar as excecption_short_ar, ket.short_desc_en as excecption_short_en
                                    , ifnull(ket.long_desc_ar, ket.short_desc_ar) as excecption_long_ar, ifnull(ket.long_desc_en, ket.short_desc_en) as excecption_long_en
                                    , ke.mtp_id
            from kpi_def kd, cte, kpi_exception ke, kpi_exception_type ket, users u_co where
                    cte.id = kd.child_subtenant_id and
                    u_co.id = kd.user_of_coordination and
                    ket.id = ke.exception_type and
                    ke.kpi_id = kd.id and
                    (ke.mtp_id = $mtp_id or ke.mtp_id is null)
            order by kd.subtenant_id, kd.child_subtenant_id, kd.id, ke.id
            ;
            
            ")
            ->pipe(new CalculatedColumn(array(
                "id"=>"{#}+1",
                "name"=>array(
                    "exp"=>function($data){
                        return $data["user_coord_name"]." ".$data["user_coord_lname"];
                    }),

                    "active_status_ar"=>array(
                        "exp"=>function($data){
                            $transtable = $this->dataStore('translation');

                            if($data["active_status"]==1)
                            $v=$transtable->where('key_name', 'active')->get(0, "value_ar");
                            else
                            $v=$transtable->where('key_name', 'inactive')->get(0, "value_ar");

                            return  $v;
                        }),
                        "active_status_en"=>array(
                            "exp"=>function($data){
                                $transtable = $this->dataStore('translation');

                                if($data["active_status"]==1)
                                    $v=$transtable->where('key_name', 'active')->get(0, "value_en");
                                else
                                    $v=$transtable->where('key_name', 'inactive')->get(0, "value_en");
                                    return  $v;
                            }),
            )))
            // -> pipe(new CalculatedColumn(array(
            //     "name"=>"{} {user_coord_lname}")))
        
            ->saveTo($source);

            
            $source->pipe(new Pivot(array(
                "dimensions"=>array(
                  "row"=>"org_unit_name,kpi_name,active_status_en,name,id,excecption_short_en"
                ),

                
            )))
            ->saveTo($node1);
        
            $node1->pipe($this->dataStore('user_details')); 
 


            $source->pipe(new Pivot(array(
                "dimensions"=>array(
                  "row"=>"org_unit_name,kpi_name,active_status_ar,name,id,excecption_short_ar"
                ),

                
            )))
            ->saveTo($node2);
        
            $node2->pipe($this->dataStore('user_details_ar')); 
 
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
            // ->pipe($this->dataStore('mtp1'));
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

<?php
namespace Modules\ClientApp\Reports;
use \koolreport\pivot\processes\Pivot;
use \koolreport\processes\Filter;
use \koolreport\processes\ColumnMeta;
use \koolreport\processes\CalculatedColumn;
use \koolreport\processes\Group;
use \koolreport\processes\ColumnRename;
use \koolreport\processes\Sort;
use \koolreport\processes\Custom;
use \koolreport\processes\Limit;
use \koolreport\pivot\processes\PivotExtract;
use \koolreport\processes\OnlyColumn;
use \koolreport\processes\CopyColumn;
use \koolreport\processes\ValueMap;
use \koolreport\cleandata\FillNull;

error_reporting (E_ALL ^ E_NOTICE); 

class KpiPerformanceReport extends \koolreport\KoolReport
{
    use \koolreport\clients\jQuery;
    use \koolreport\clients\Bootstrap;
    use \koolreport\clients\FontAwesome;

    use \koolreport\inputs\Bindable;
    use \koolreport\inputs\POSTBinding;


    protected $language;
    function __construct(array $params = array())
    {
        $this->sect = $this->params['sect'];
        $this->org = $this->params['org'];
        $this->language = $params['language'];
        // $this->test=$params['test'];
         parent::__construct($params);
    }

    protected function defaultParamValues()
    {
        return array(
            "sector" => "",
            "section" => "",
            "mtp" => 4,
            "periodicity" => 'Y',
            "year_no" => 1,
            "top_performing" => 5,
            "performing"=>80,
            "nonperforming"=>50,
            "expand"=>"",
          //  "filter"=>1,
            "expand1"=>"",
            "radiolist"=>"3",
            "radiolist1"=>"3",
            // "test"=>array(1,2,3),
            "debug_modeperf"=>true,


        );
    }

    protected function bindParamsToInputs()
    {
        return array(
            "sector",
            "section",
            "mtp",
            "periodicity",
            "year_no",
            "top_performing",
            "performing",
            "nonperforming",
            "expand",
            // "filter",
            "expand1",
            "radiolist",
            "radiolist1",
            "debug_modeperf",

// "test",

        );
    }

    public function settings()
    {
        return array(

            "dataSources" => array(
                "mysql1" => array(
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
    
            $limit='';
            $language = '';
            if (isset($this->params['language']) && !empty($this->params['language'])) {
                $language = $this->params['language'];
            }

        if(isset($_POST['expand']))
        {
            $this->params["radiolist"]=$this->params["radiolist1"]   ;
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
        // $date2=date("Y-m-d");
        $limit=$this->params['top_performing'];

        $performing_id=$this->params['performing']/100;
        $nonperforming_id=$this->params['nonperforming']/100;

        // if($this->params['expand']==2)
        // {
        //     $this->params['radiolist']=$this->params['radiolist1'];
        // }
    //   echo $this->params['expand'];
     //  ->query("set @period_name = 'Y';")
            //  ->query("set @year_no = 1;")
            //  ->query(" set @mtp_id = 4;")
            //  ->query("  set @sub_id = 114;")
            //  /**set your arg here, value list is {Q1, Q2, Q3, Q4, H1, H2, Y} for quarter/half annual/annual**/
            /**set your arg here**/
    // var_dump($this->params["radiolist"]);
    //----------------------------------------performing--------------------------------   
    $this->src("mysql1")->query("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
        -- This is end of the recursion: Select low level
        select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
            from subtenant where
            id = $parent_id
        UNION ALL
        -- This is the recursive part: It joins to cte        
        select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
            from subtenant s
            inner join cte c on s.parent_id = c.id
        )
        -- select id, name, subtenant_type, parent_id
        select 	cte.level1_id as sub_id_level1, cte.id as sub_id, cte.parent_id as sub_parent_id, cte.subtenant_type as sub_type, cte.name as sub_name,
                                kd.id kpi_id, kd.symbol as kpi_symbol, kd.name as kpi_name,
                                kd.importance as kpi_importance,
                                case lower(:periodicity_id)
                                            when 'q1' then kvs.q1_perf
                                            when 'q2' then kvs.q2_perf
                                            when 'q3' then kvs.q3_perf
                                            when 'q4' then kvs.q4_perf
                                            when 'h1' then kvs.h1_perf
                                            when 'h2' then kvs.h2_perf
                                            when 'y' then kvs.y_perf				
                                            else null
                                    end as kpi_perf,
                                    case lower(:periodicity_id)
                                            when 'q1' then kvs.q1_prog
                                            when 'q2' then kvs.q2_prog
                                            when 'q3' then kvs.q3_prog
                                            when 'q4' then kvs.q4_prog
                                            when 'h1' then kvs.h1_prog
                                            when 'h2' then kvs.h2_prog
                                            when 'y' then kvs.y_prog				
                                            else null
                                    end as kpi_prog
        from cte, kpi_values_stats kvs, kpi_target kt, kpi_def kd where
        kd.child_subtenant_id = cte.id and
        kt.kpi_id = kd.id and
        kt.mtp_id = :mtp_id and
        kvs.kpi_target_id = kt.id and
        kvs.year_no = :year_id and
        case lower(:periodicity_id)
                                            when 'q1' then kvs.q1_perf
                                            when 'q2' then kvs.q2_perf
                                            when 'q3' then kvs.q3_perf
                                            when 'q4' then kvs.q4_perf
                                            when 'h1' then kvs.h1_perf
                                            when 'h2' then kvs.h2_perf
                                            when 'y' then kvs.y_perf				
                                            else null
        end >= $performing_id
        order by kpi_perf desc limit $limit;")
    ->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"],":year_id" => $this->params["year_no"], ":periodicity_id" => $this->params["periodicity"],":performing_id" => $this->params["performing"],":limit" => $this->params["top_performing"]))
    

           ->pipe(new ColumnMeta(array(

                "kpi_perf1"=>array(
                    "type"=>"number",
                    // "label"=>"Order Date",
                    // 'suffix' => "%",
                )
                                )))

           
            // ->pipe(new Sort(array(
            //     "kpi_perf"=>"desc",
            //     // "name"=>"desc"
            // )))
            ->pipe(new CopyColumn(array(
                "kpi_perf1"=>"kpi_perf",
                // "copy_of_amount"=>"amount"
            )))
            ->pipe(new ValueMap(array(
                "kpi_perf1" =>array(
                    "{func}"=>function($value){
                        // switch ($value) {
                            static $i=1;
                            $i++;	
                            return $i;	
                            
                    }			
                )
            )))
          ->pipe(new Custom(function($row){
                if($row["kpi_perf"]!=NULL){
                $perf_value = $row["kpi_perf"]*100;
                $row["kpi_perf"]= round($perf_value, 2);
                if($this->params['debug_modeperf']==true && $row["kpi_perf"]>100)
                $row["kpi_perf"]=100;


                }
                return $row;
            }))
            
            ->pipe(new \koolreport\processes\Map([
                '{value}' => function ($row, $meta, $index, $mapState) {
                    if(isset($row['kpi_perf'])) {
                        $row['kpi_perf'] = $row['kpi_perf'].'%';
                    }
                    return $row;
                },

            ]))
            // ->pipe(new Limit(array($this->params['top_performing'])))
            ->pipe(new CalculatedColumn(array(
                "id"=>"{#}+1",
                "perf_name"=>array(
                    "exp"=>function($data){
                        if($data!=NULL){
                        $perdata = str_replace("%", "", $data["kpi_perf"]);
                        // if(($perdata<$this->params["nonperforming"])&&($perdata!=NULL))
                        // {
                        //     $translation = ($this->params['language'] == 'en' ? 'Not Performing' : 'غير مؤدي');
                        //     return $translation;
                        // }
                        if(($perdata>=$this->params["performing"])&&($perdata!=NULL))
                        {
                            $translation = ($this->params['language'] == 'en' ?'Performing' : 'مؤدي');
                            return $translation;
                        }
                        else
                        return "rest";
                        }
                        return NULL;
                    }),

            )))

            ->pipe(new OnlyColumn(array(
                "perf_name",
                "sub_name",
                "kpi_name",
                "kpi_symbol",
                "kpi_perf",
                "kpi_perf1"
            )))
            // ->pipe(new Filter(array(
            //     array("perf_name","=","Performing"),
            //     "or",
            //     array("perf_name","=","مؤدي"),


            // )))
            // ->pipe(new Sort(array(

            //     "kpi_perf"=>"desc"
            //  )))
            //  ->pipe(new Limit(array($this->params['top_performing'])))

            ->saveTo($node_perf);
            // ->pipe(new Sort(array(
            //     "kpi_perf"=>"desc"
            // )))

            $node_perf->pipe(new Pivot(array(
                "dimensions"=>array(
                  "row"=>"perf_name,sub_name,kpi_symbol,kpi_name,kpi_perf",
                    // "column"=>"kpi_symbol,kpi_name,next_reading_date"
                ),
                'aggregates'=>array(
                    'sum'=>'kpi_perf1',
                    'count'=>'kpi_perf'
                )

            )))->saveTo($node3);
//------------------barchart-count-----------------------------------
            $node3->pipe(new PivotExtract(array(
                "row" => array(
                    "parent" => array(),
                ),
                "column" => array(
                    "parent" => array(
                    ),
                ),
                "measures"=>array(
                    "kpi_perf - count", 
                ),
            )))
            ->pipe($this->dataStore('chartTable1'));
            $node3->pipe($this->dataStore('performing'));


            // $source ->pipe(new Filter(array(
            //     array("perf_name","=","nonperforming")
            // )))


            $node_perf->pipe($this->dataStore('performing1'));


            $node_perf->pipe(new Group(array(
                "by"=>"sub_name",
                "count"=>"kpi_perf"
            )))
            // ->pipe(new FillNull(array(
            //     "newValue"=>""
            // )))
            ->pipe($this->dataStore('performing11'));


        //  var_dump($this->params['radiolist']);
        //------------------nonperforming(node2)--------------------------




        $this->src("mysql1")
        ->query("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
            -- This is end of the recursion: Select low level
            select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
                from subtenant where
                id = $parent_id
            UNION ALL
            -- This is the recursive part: It joins to cte        
            select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
                from subtenant s
                inner join cte c on s.parent_id = c.id
            )
            -- select id, name, subtenant_type, parent_id
            select 	cte.level1_id as sub_id_level1, cte.id as sub_id, cte.parent_id as sub_parent_id, cte.subtenant_type as sub_type, cte.name as sub_name,
                                    kd.id kpi_id, kd.symbol as kpi_symbol, kd.name as kpi_name,
                                    kd.importance as kpi_importance,
                                    case lower(:periodicity_id)
                                                when 'q1' then kvs.q1_perf
                                                when 'q2' then kvs.q2_perf
                                                when 'q3' then kvs.q3_perf
                                                when 'q4' then kvs.q4_perf
                                                when 'h1' then kvs.h1_perf
                                                when 'h2' then kvs.h2_perf
                                                when 'y' then kvs.y_perf				
                                                else null
                                        end as kpi_perf,
                                        case lower(:periodicity_id)
                                                when 'q1' then kvs.q1_prog
                                                when 'q2' then kvs.q2_prog
                                                when 'q3' then kvs.q3_prog
                                                when 'q4' then kvs.q4_prog
                                                when 'h1' then kvs.h1_prog
                                                when 'h2' then kvs.h2_prog
                                                when 'y' then kvs.y_prog				
                                                else null
                                        end as kpi_prog
            from cte, kpi_values_stats kvs, kpi_target kt, kpi_def kd where
            kd.child_subtenant_id = cte.id and
            kt.kpi_id = kd.id and
            kt.mtp_id = :mtp_id and
            kvs.kpi_target_id = kt.id and
            kvs.year_no = :year_id and
            case lower(:periodicity_id)
                                                when 'q1' then kvs.q1_perf
                                                when 'q2' then kvs.q2_perf
                                                when 'q3' then kvs.q3_perf
                                                when 'q4' then kvs.q4_perf
                                                when 'h1' then kvs.h1_perf
                                                when 'h2' then kvs.h2_perf
                                                when 'y' then kvs.y_perf				
                                                else null
            end < $nonperforming_id
            order by kpi_perf asc limit $limit;")
        ->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"],":year_id" => $this->params["year_no"], ":periodicity_id" => $this->params["periodicity"], ":nonperforming_id" => $this->params["nonperforming"],":limit" => $this->params["top_performing"]))
        

   ->pipe(new ColumnMeta(array(

        "kpi_perf1"=>array(
            "type"=>"number",
            // "label"=>"Order Date",
            // 'suffix' => "%",
        )
                        )))

    ->pipe(new Custom(function($row){
        if($row["kpi_perf"]!=NULL){
        $perf_value = $row["kpi_perf"]*100;
        $row["kpi_perf"]= round($perf_value, 2);
        if($this->params['debug_modeperf']==true && $row["kpi_perf"]>100)
        $row["kpi_perf"]=100;


        }
        return $row;
    }))

    ->pipe(new CopyColumn(array(
        "kpi_perf1"=>"kpi_perf",
        // "copy_of_amount"=>"amount"
    )))
    ->pipe(new ValueMap(array(
        "kpi_perf1" =>array(
            "{func}"=>function($value){
                // switch ($value) {
                    static $i=1;
                    $i++;	
                    return $i;	
                    
            }			
        )
    )))
    ->pipe(new Sort(array(
        "kpi_perf1"=>"asc",
        // "name"=>"desc"
    )))
    // ->pipe(new Limit(array($this->params['top_performing'])))
    ->pipe(new \koolreport\processes\Map([
        '{value}' => function ($row, $meta, $index, $mapState) {
            if(isset($row['kpi_perf'])) {
                $row['kpi_perf'] = $row['kpi_perf'].'%';
            }
            return $row;
        },

    ]))
    ->pipe(new CalculatedColumn(array(
        "id"=>"{#}+1",
        "perf_name"=>array(
            "exp"=>function($data){
                if($data!=NULL){
                    $perdata = str_replace("%", "", $data["kpi_perf"]);
                    if(($perdata<($this->params["nonperforming"]))&&($perdata!=NULL))
                    {
                        $translation = ($this->params['language'] == 'en' ? 'Not Performing' : 'غير مؤدي');
                        return $translation;
                    }
                    // else if(($perdata>=($this->params["performing"]))&&($perdata!=NULL))
                    // {
                    //     $translation = ($this->params['language'] == 'en' ?'Performing' : 'مؤدي');
                    //     return $translation;
                    // }
                    else
                    return "rest";
                }
                else
                return NULL;
            }),

    )))


    ->saveTo($source_nonperf);

    $source_nonperf ->pipe(new OnlyColumn(array(
        "perf_name",
        "sub_name",
        "kpi_name",
        "kpi_symbol",
        "kpi_perf",
        "kpi_perf1"
    )))
    // ->pipe(new Filter(array(
    //     array("perf_name","=","Not Performing"),
    //     "or",
    //     array("perf_name","=","غير مؤدي"),

    // )))
    // ->pipe(new Sort(array(

    //     "kpi_perf"=>"asc"
    //  )))
    // ->pipe(new Limit(array($this->params['top_performing'])))

     ->saveTo($node_nonperf)
        // ->pipe($source)


    ->pipe(new Pivot(array(
        "dimensions"=>array(
          "row"=>"perf_name,sub_name,kpi_symbol,kpi_name,kpi_perf",
            // "column"=>"kpi_symbol,kpi_name,next_reading_date"
        ),
        'aggregates'=>array(
            'sum'=>'kpi_perf1',
            'count'=>'kpi_perf'
        )

    )))->saveTo($node2);

    $node2->pipe($this->dataStore('nonperforming'));
//node2
//--------------------------Barchart Count nonperf----------------------
$node2->pipe(new PivotExtract(array(
    "row" => array(
        "parent" => array(),
    ),
    "column" => array(
        "parent" => array(
        ),
    ),
    "measures"=>array(
        "kpi_perf - count", 
    ),
)))
->pipe($this->dataStore('chartTable2'));
//------------------------------------------------------------
$node_nonperf->pipe($this->dataStore('nonperforming1'));
//node_perf
    $node_nonperf->pipe(new ColumnRename(array(
        "kpi_perf"=>"kpi_nonperf",
    )))
    ->pipe(new Group(array(
        "by"=>"sub_name",
        "count"=>"kpi_nonperf"
    )))
    // ->pipe(new FillNull(array(
    //     "newValue"=>""
    // )))
    ->pipe($this->dataStore('nonperforming11'));
//----------------------------------------combined------------------------------
  

    
$this->src("mysql1")
->query("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
		from subtenant where
        id = $parent_id
	UNION ALL
    -- This is the recursive part: It joins to cte        
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
		from subtenant s
        inner join cte c on s.parent_id = c.id
	)
	-- select id, name, subtenant_type, parent_id
	select 	cte.level1_id as sub_id_level1, cte.id as sub_id, cte.parent_id as sub_parent_id, cte.subtenant_type as sub_type, cte.name as sub_name,
							kd.id kpi_id, kd.symbol as kpi_symbol, kd.name as kpi_name,
							kd.importance as kpi_importance,
							case lower(:periodicity_id)
										when 'q1' then kvs.q1_perf
										when 'q2' then kvs.q2_perf
										when 'q3' then kvs.q3_perf
										when 'q4' then kvs.q4_perf
										when 'h1' then kvs.h1_perf
										when 'h2' then kvs.h2_perf
										when 'y' then kvs.y_perf				
										else null
								end as kpi_perf,
								case lower(:periodicity_id)
										when 'q1' then kvs.q1_prog
										when 'q2' then kvs.q2_prog
										when 'q3' then kvs.q3_prog
										when 'q4' then kvs.q4_prog
										when 'h1' then kvs.h1_prog
										when 'h2' then kvs.h2_prog
										when 'y' then kvs.y_prog				
										else null
								end as kpi_prog
	from cte, kpi_values_stats kvs, kpi_target kt, kpi_def kd where
	kd.child_subtenant_id = cte.id and
	kt.kpi_id = kd.id and
	kt.mtp_id = :mtp_id and
	kvs.kpi_target_id = kt.id and
	kvs.year_no = :year_id and
	case lower(:periodicity_id)
										when 'q1' then kvs.q1_perf
										when 'q2' then kvs.q2_perf
										when 'q3' then kvs.q3_perf
										when 'q4' then kvs.q4_perf
										when 'h1' then kvs.h1_perf
										when 'h2' then kvs.h2_perf
										when 'y' then kvs.y_perf				
										else null
	end >= $performing_id
	order by kpi_perf desc limit $limit;")
->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"],":year_id" => $this->params["year_no"], ":periodicity_id" => $this->params["periodicity"],":performing_id" => $this->params["performing"],":limit" => $this->params["top_performing"]))
// ->pipe(new Filter(array(
//     array("kpi_perf",">=",0.8)
// )))
// ->pipe(new Limit(array($this->params['top_performing'])))
->saveTo($nodex);

$this->src("mysql1")
->query("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
		from subtenant where
        id = $parent_id
	UNION ALL
    -- This is the recursive part: It joins to cte        
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
		from subtenant s
        inner join cte c on s.parent_id = c.id
	)
	-- select id, name, subtenant_type, parent_id
	select 	cte.level1_id as sub_id_level1, cte.id as sub_id, cte.parent_id as sub_parent_id, cte.subtenant_type as sub_type, cte.name as sub_name,
							kd.id kpi_id, kd.symbol as kpi_symbol, kd.name as kpi_name,
							kd.importance as kpi_importance,
							case lower(:periodicity_id)
										when 'q1' then kvs.q1_perf
										when 'q2' then kvs.q2_perf
										when 'q3' then kvs.q3_perf
										when 'q4' then kvs.q4_perf
										when 'h1' then kvs.h1_perf
										when 'h2' then kvs.h2_perf
										when 'y' then kvs.y_perf				
										else null
								end as kpi_perf,
								case lower(:periodicity_id)
										when 'q1' then kvs.q1_prog
										when 'q2' then kvs.q2_prog
										when 'q3' then kvs.q3_prog
										when 'q4' then kvs.q4_prog
										when 'h1' then kvs.h1_prog
										when 'h2' then kvs.h2_prog
										when 'y' then kvs.y_prog				
										else null
								end as kpi_prog
	from cte, kpi_values_stats kvs, kpi_target kt, kpi_def kd where
	kd.child_subtenant_id = cte.id and
	kt.kpi_id = kd.id and
	kt.mtp_id = :mtp_id and
	kvs.kpi_target_id = kt.id and
	kvs.year_no = :year_id and
	case lower(:periodicity_id)
										when 'q1' then kvs.q1_perf
										when 'q2' then kvs.q2_perf
										when 'q3' then kvs.q3_perf
										when 'q4' then kvs.q4_perf
										when 'h1' then kvs.h1_perf
										when 'h2' then kvs.h2_perf
										when 'y' then kvs.y_perf				
										else null
	end < $nonperforming_id
	order by kpi_perf asc limit $limit;")
->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"],":year_id" => $this->params["year_no"], ":periodicity_id" => $this->params["periodicity"], ":nonperforming_id" => $this->params["nonperforming"],":limit" => $this->params["top_performing"]))

// ->pipe(new Limit(array($this->params['top_performing'])))

->pipe($nodex);
$nodex->pipe(new ColumnMeta(array(

    "kpi_perf1"=>array(
        "type"=>"number",
      
    )
                    )))

->pipe(new Custom(function($row){
    if($row["kpi_perf"]!=NULL){
    $perf_value = $row["kpi_perf"]*100;
    $row["kpi_perf"]= round($perf_value, 2);
    if($this->params['debug_modeperf']==true && $row["kpi_perf"]>100)
    $row["kpi_perf"]=100;


    }
    return $row;
}))
->pipe(new CopyColumn(array(
    "kpi_perf1"=>"kpi_perf",
    // "copy_of_amount"=>"amount"
)))
->pipe(new ValueMap(array(
    "kpi_perf1" =>array(
        "{func}"=>function($value){
            // switch ($value) {
                static $i=1;
                $i++;	
                return $i;	
                
        }			
    )
)))
->pipe(new Sort(array(
    "kpi_perf1"=>"asc",
    // "name"=>"desc"
)))
->pipe(new \koolreport\processes\Map([
    '{value}' => function ($row, $meta, $index, $mapState) {
        if(isset($row['kpi_perf'])) {
            $row['kpi_perf'] = $row['kpi_perf'].'%';
        }
        return $row;
    },

]))
// ->pipe(new Limit(array($this->params['top_performing'])))
->pipe(new CalculatedColumn(array(
    "id"=>"{#}+1",
    "perf_name"=>array(
        "exp"=>function($data){
            if($data!=NULL){
            $perdata = str_replace("%", "", $data["kpi_perf"]);
            if(($perdata<$this->params["nonperforming"])&&($perdata!=NULL))
            {
                $translation = ($this->params['language'] == 'en' ? 'Not Performing' : 'غير مؤدي');
                return $translation;
            }
            else if(($perdata>=$this->params["performing"])&&($perdata!=NULL))
            {
                $translation = ($this->params['language'] == 'en' ?'Performing' : 'مؤدي');
                return $translation;
            }
            else
            return "rest";
            }
            else
            return NULL;
        }),
      
)))
// ->pipe(new Filter(array(
//     array("perf_name","!=","rest")
// )))

 ->pipe(new Pivot(array(
        "dimensions"=>array(
          "row"=>"perf_name,sub_name,kpi_symbol,kpi_name,kpi_perf",
            // "column"=>"kpi_symbol,kpi_name,next_reading_date"
        ),
        'aggregates'=>array(
            'sum'=>'kpi_perf1',
            'count'=>'kpi_perf1'
        )

    )))
->pipe($this->dataStore("combined"));

//----------------------------
    $this->src("mysql1")
            ->query("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3")
            ->pipe($this->dataStore('sector1'));

        if ($this->params["sector"] != null) {
            $this->src("mysql1")
                ->query("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '_') from subtenant where parent_id = :sector_id UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('_', 50), :sector_id, '', CONCAT(id, '') from subtenant where parent_id = :sector_id) select id, name from cte order by path")
                ->params(array(":sector_id" => $this->params["sector"]))
                ->pipe($this->dataStore('section1'));
        }
        $this->src("mysql1")
            ->query("select id,name from mtp")
            // ->pipe($this->dataStore('mtp1'));
        ->pipe($this->dataStore('mtp1'));

        $this->src("mysql1")
            ->query("select id, name from kpi_cat")
            ->pipe($this->dataStore('category1'));

        $this->src("mysql1")
            ->query("select * from trans_table")
            ->pipe($this->dataStore('translation'))->requestDataSending();
        $this->src("mysql1")
            ->query("select id,name from subtenant where id=$parent_id")
            ->pipe($this->dataStore('sector_name'))->requestDataSending();
    }
}

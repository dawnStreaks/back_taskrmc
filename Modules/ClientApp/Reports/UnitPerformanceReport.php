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

class UnitPerformanceReport extends \koolreport\KoolReport
{
    use \koolreport\clients\jQuery;
    use \koolreport\clients\Bootstrap;
    use \koolreport\clients\FontAwesome;

    use \koolreport\inputs\Bindable;
    use \koolreport\inputs\POSTBinding;


    protected $language;
    function __construct(array $params = array())
    {

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
            "sector1" => "",
            "section1" => "",
            "mtp1" => 4,
            "periodicity1" => 'Y',
            "year_no1" => 1,
            "top_performing1" => 5,
            "performing1"=>80,
            "nonperforming1"=>50,
            "debug_modeperf"=>true,

            // "mtp" => 4,
            // "periodicity" => 'Y',
            // "year_no" => 1,
            // "top_performing" => 5,
            // "performing"=>80,
            // "nonperforming"=>50,
            // "test"=>array(1,2,3),

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
            "sector1",
            "section1",
            "mtp1",
            "periodicity1",
            "year_no1",
            "top_performing1",
            "performing1",
            "nonperforming1",
            "debug_modeperf",

// "test",

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
            $limit='';
        
// var_dump($this->params['section']);
            $language = '';
            if (isset($this->params['language']) && !empty($this->params['language'])) {
                $language = $this->params['language'];
            }

        if(isset($_POST['expand']))
        {
           
            $this->params["radiolist"]=$this->params["radiolist1"];
            $this->params["sector"]=$this->params["sector1"]   ;
            $this->params["section"]=$this->params["section1"]   ;
            $this->params["mtp"]=$this->params["mtp1"];
            $this->params["periodicity"]=$this->params["periodicity1"]   ;
            $this->params["year_no"]=$this->params["year_no1"]   ;
            $this->params["top_performing"]=$this->params["top_performing1"];
            $this->params["performing"]=$this->params["performing1"]   ;
            $this->params["nonperforming"]=$this->params["nonperforming1"]   ;

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
    $this->src("mysql")
    // ->query("call p_unit_perf_prog_recursive_limit(2,4,'Y',1,0.8,0.5,5);")
    ->query("CALL p_unit_perf_prog_recursive_limit($parent_id,:mtp_id,:periodicity_id,:year_id,$performing_id,NULL,:limit);")
    ->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"],":year_id" => $this->params["year_no"], ":periodicity_id" => $this->params["periodicity"],":performing_id" => $this->params["performing"],":limit" => $this->params["top_performing"]))
    
    
    

           ->pipe(new ColumnMeta(array(

                "perf_sum_w1"=>array(
                    "type"=>"number",
                )
                                )))

            ->pipe(new CopyColumn(array(
                "perf_sum_w1"=>"perf_sum_w",
                // "copy_of_amount"=>"amount"
            )))
            ->pipe(new ValueMap(array(
                "perf_sum_w1" =>array(
                    "{func}"=>function($value){
                        // switch ($value) {
                            static $i=1;
                            $i++;	
                            return $i;	
                            
                    }			
                )
            )))
            //  ->pipe(new Sort(array(
            //     "perf_sum_w1"=>"desc",
            //     // "name"=>"desc"
            // )))
          ->pipe(new Custom(function($row){
                if($row["perf_sum_w"]!=NULL){
                $perf_value = $row["perf_sum_w"]*100;
                $row["perf_sum_w"]= round($perf_value, 2);
                if($this->params['debug_modeperf']==true &&  $row["perf_sum_w"]>100)
                $row["perf_sum_w"]=100;


                }
                if($row["dept_name"]==NULL){
                    $row["dept_name"]="";
                }
                if($row["sub_name"]==NULL){
                    $row["sub_name"]="";
                }
                if($row["sector_name"]==NULL){
                    $row["sector_name"]="";
                }
                return $row;
            }))
            ->pipe(new \koolreport\processes\Map([
                '{value}' => function ($row, $meta, $index, $mapState) {
                    if(isset($row['perf_sum_w'])) {
                        $row['perf_sum_w'] = $row['perf_sum_w'].'%';
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
                        $perdata = str_replace("%", "", $data["perf_sum_w"]);
                        if(($perdata<$this->params["nonperforming"])&&($perdata!=NULL))
                        {
                            $translation = ($this->params['language'] == 'en' ? 'Not Performing' : 'غير مؤدي');
                            return $translation;
                        }
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
                "sector_name",
                "dept_name",
                "sub_name",
                // "kpi_name",
                // "kpi_symbol",
                "perf_sum_w",
                "perf_sum_w1"
        )))
            ->pipe(new Filter(array(
                array("perf_name","!=","rest"),
            //     
            )))
            // ->pipe(new Sort(array(

            //     "perf_sum_w"=>"desc"
            //  )))
            //  ->pipe(new Limit(array($this->params['top_performing'])))
            // ->pipe($this->dataStore('test'))

            ->saveTo($node_perf);
            // ->pipe(new Sort(array(
            //     "perf_sum_w"=>"desc"
            // )))

            $node_perf->pipe(new Pivot(array(
                "dimensions"=>array(
                  "row"=>"perf_name,sector_name,dept_name,sub_name,perf_sum_w",
                    // "column"=>"kpi_symbol,kpi_name,next_reading_date"
                ),
                'aggregates'=>array(
                    'sum'=>'perf_sum_w1',
                    'count'=>'perf_sum_w'
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
                    "perf_sum_w - count", 
                ),
            )))
            ->pipe($this->dataStore('chartTable1'));
            $node3->pipe($this->dataStore('performing'));


            // $source ->pipe(new Filter(array(
            //     array("perf_name","=","nonperforming")
            // )))

        //    if($this->params['section']=="")
            $node_perf->pipe($this->dataStore('performing1'));

            if((isset($_POST['sector']))&&(!empty($_POST['sector'])))
            {
                if($this->params['section']!="")
                {
                    $node_perf->pipe(new Group(array(
                        "by"=>"sub_name",
                        "count"=>"perf_sum_w"
                        )))
                        ->pipe($this->dataStore('performing11'));

                }
                else{
                    $node_perf->pipe(new Group(array(
                        "by"=>"dept_name",
                        "count"=>"perf_sum_w"
                        )))
                        ->pipe($this->dataStore('performing11'));

                }
           
            
            
            }
            if(empty($_POST['sector'])){
                $node_perf->pipe(new Group(array(
                    "by"=>"sector_name",
                    "count"=>"perf_sum_w"
                )))
                // ->pipe(new FillNull(array(
                //     "newValue"=>""
                // )))
                ->pipe($this->dataStore('performing11'));
            }


            if((isset($_POST['sector']))&&(!empty($_POST['sector'])))
            {
                if($this->params['section']!="")
                {
                    $node_perf->pipe(new Group(array(
                        "by"=>"sub_name",
                        "count"=>"sub_name"
                        )))
                        ->pipe($this->dataStore('performing1pie'));

                }
                else{
                    $node_perf->pipe(new Group(array(
                        "by"=>"dept_name",
                        "count"=>"dept_name"
                        )))
                        ->pipe($this->dataStore('performing1pie'));

                }
           
            
            
            }
            if(empty($_POST['sector'])){
                $node_perf->pipe(new Group(array(
                    "by"=>"sector_name",
                    "count"=>"sector_name"
                )))
                // ->pipe(new FillNull(array(
                //     "newValue"=>""
                // )))
                ->pipe($this->dataStore('performing1pie'));
            }



        //  var_dump($this->params['radiolist']);
        //------------------nonperforming(node2)--------------------------




        $this->src("mysql")
        ->query("CALL p_unit_perf_prog_recursive_limit($parent_id,:mtp_id,:periodicity_id,:year_id,NULL,$nonperforming_id,:limit);")
        ->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"],":year_id" => $this->params["year_no"], ":periodicity_id" => $this->params["periodicity"], ":nonperforming_id" => $this->params["nonperforming"],":limit" => $this->params["top_performing"]))
        

   ->pipe(new ColumnMeta(array(

        "perf_sum_w1"=>array(
            "type"=>"number",
            // "label"=>"Order Date",
            // 'suffix' => "%",
        )
                        )))

    ->pipe(new Custom(function($row){
        if($row["perf_sum_w"]!=NULL){
        $perf_value = $row["perf_sum_w"]*100;
        $row["perf_sum_w"]= round($perf_value, 2);
        if($this->params['debug_modeperf']==true &&  $row["perf_sum_w"]>100)
        $row["perf_sum_w"]=100;


        }
        if($row["dept_name"]==NULL){
            $row["dept_name"]="";
        }
        if($row["sub_name"]==NULL){
            $row["sub_name"]="";
        }
        if($row["sector_name"]==NULL){
            $row["sector_name"]="";
        }
        return $row;
    }))

    ->pipe(new CopyColumn(array(
        "perf_sum_w1"=>"perf_sum_w",
        // "copy_of_amount"=>"amount"
    )))
    ->pipe(new ValueMap(array(
        "perf_sum_w1" =>array(
            "{func}"=>function($value){
                // switch ($value) {
                    static $i=1;
                    $i++;	
                    return $i;	
                    
            }			
        )
    )))
    ->pipe(new Sort(array(
        "perf_sum_w1"=>"asc",
        // "name"=>"desc"
    )))
    // ->pipe(new Limit(array($this->params['top_performing'])))
    ->pipe(new \koolreport\processes\Map([
        '{value}' => function ($row, $meta, $index, $mapState) {
            if(isset($row['perf_sum_w'])) {
                $row['perf_sum_w'] = $row['perf_sum_w'].'%';
            }
            return $row;
        },

    ]))
    ->pipe(new CalculatedColumn(array(
        "id"=>"{#}+1",
        "perf_name"=>array(
            "exp"=>function($data){
                if($data!=NULL){
                    $perdata = str_replace("%", "", $data["perf_sum_w"]);
                    if(($perdata<($this->params["nonperforming"]))&&($perdata!=NULL))
                    {
                        $translation = ($this->params['language'] == 'en' ? 'Not Performing' : 'غير مؤدي');
                        return $translation;
                    }
                    else if(($perdata>=($this->params["performing"]))&&($perdata!=NULL))
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


    ->saveTo($source_nonperf);

    $source_nonperf ->pipe(new OnlyColumn(array(
        "perf_name",
        "sector_name",
        "dept_name",

        "sub_name",
        // "sector_name",
        "perf_sum_w",
        "perf_sum_w1"
    )))
    ->pipe(new Filter(array(
        array("perf_name","!=","rest"),
    //     
    )))
    // ->pipe(new Filter(array(
    //     array("perf_name","=","Not Performing"),
    //     "or",
    //     array("perf_name","=","غير مؤدي"),

    // )))
    // ->pipe(new Sort(array(

    //     "perf_sum_w"=>"asc"
    //  )))
    // ->pipe(new Limit(array($this->params['top_performing'])))

     ->saveTo($node_nonperf)
        // ->pipe($source)


    ->pipe(new Pivot(array(
        "dimensions"=>array(
            "row"=>"perf_name,sector_name,dept_name,sub_name,perf_sum_w",
            // "column"=>"kpi_symbol,kpi_name,next_reading_date"
        ),
        'aggregates'=>array(
            'sum'=>'perf_sum_w1',
            'count'=>'perf_sum_w'
        )

    )))->saveTo($node2);
    // if($this->params['section']=="")
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
        "perf_sum_w - count", 
    ),
)))
->pipe($this->dataStore('chartTable2'));
//------------------------------------------------------------
$node_nonperf->pipe($this->dataStore('nonperforming1'));
//node_perf

if(isset($_POST['sector'])&&(!empty($_POST['sector'])))
{
    if($this->params['section']!="")
    {

    $node_nonperf->pipe(new ColumnRename(array(
        "perf_sum_w"=>"nonperf_sum_w",
    )))
    ->pipe(new Group(array(
        "by"=>"sub_name",
        "count"=>"nonperf_sum_w"
    )))
    // ->pipe(new FillNull(array(
    //     "newValue"=>""
    // )))
    ->pipe($this->dataStore('nonperforming11'));
    }
    else{

    $node_nonperf->pipe(new ColumnRename(array(
        "perf_sum_w"=>"nonperf_sum_w",
    )))
    ->pipe(new Group(array(
        "by"=>"dept_name",
        "count"=>"nonperf_sum_w"
    )))
    // ->pipe(new FillNull(array(
    //     "newValue"=>""
    // )))
    ->pipe($this->dataStore('nonperforming11'));
    

    }
}
if(empty($_POST['sector'])){
    $node_nonperf->pipe(new ColumnRename(array(
        "perf_sum_w"=>"nonperf_sum_w",
    )))
    ->pipe(new Group(array(
        "by"=>"sector_name",
        "count"=>"nonperf_sum_w"
    )))
    // ->pipe(new FillNull(array(
    //     "newValue"=>""
    // )))
    ->pipe($this->dataStore('nonperforming11')); 

    
}
if(isset($_POST['sector'])&&(!empty($_POST['sector'])))
{
    if($this->params['section']!="")
    {

    $node_nonperf->pipe(new ColumnRename(array(
        "perf_sum_w"=>"nonperf_sum_w",
    )))
    ->pipe(new Group(array(
        "by"=>"sub_name",
        "count"=>"sub_name"
    )))
    // ->pipe(new FillNull(array(
    //     "newValue"=>""
    // )))
    ->pipe($this->dataStore('nonperforming1pie'));
    }
    else{

    $node_nonperf->pipe(new ColumnRename(array(
        "perf_sum_w"=>"nonperf_sum_w",
    )))
    ->pipe(new Group(array(
        "by"=>"dept_name",
        "count"=>"dept_name"
    )))
    // ->pipe(new FillNull(array(
    //     "newValue"=>""
    // )))
    ->pipe($this->dataStore('nonperforming1pie'));
    

    }
}
if(empty($_POST['sector'])){
    $node_nonperf->pipe(new ColumnRename(array(
        "perf_sum_w"=>"nonperf_sum_w",
    )))
    ->pipe(new Group(array(
        "by"=>"sector_name",
        "count"=>"sector_name"
    )))
    // ->pipe(new FillNull(array(
    //     "newValue"=>""
    // )))
    ->pipe($this->dataStore('nonperforming1pie')); 

    
}



//----------------------------------------combined------------------------------
  

    
$this->src("mysql")
// ->query("call p_unit_perf_prog_recursive_limit(2,4,'Y',1,0.8,0.5,5);")

->query("CALL p_unit_perf_prog_recursive_limit($parent_id,:mtp_id,:periodicity_id,:year_id,$performing_id,$nonperforming_id,:limit);")
->params(array(":sector_id" => $this->params["sector"], ":mtp_id" => $this->params["mtp"],":year_id" => $this->params["year_no"], ":periodicity_id" => $this->params["periodicity"],":performing_id" => $this->params["performing"],":limit" => $this->params["top_performing"]))
// ->pipe(new Filter(array(
//     array("perf_sum_w",">=",0.8)
// )))
// ->pipe(new Limit(array($this->params['top_performing'])))

// ->pipe(new ValueMap(array(
//     "dept_name"=>array(
//         NULL=>"-",
        
//     )
// )))
->saveTo($nodex);


// ->pipe(new Limit(array($this->params['top_performing'])))

$nodex->pipe(new ColumnMeta(array(

    "perf_sum_w1"=>array(
        "type"=>"number",
      
    )
                    )))
                   
->pipe(new Custom(function($row){
    if($row["perf_sum_w"]!=NULL){
    $perf_value = $row["perf_sum_w"]*100;
    $row["perf_sum_w"]= round($perf_value, 2);
    if($this->params['debug_modeperf']==true &&  $row["perf_sum_w"]>100)
    $row["perf_sum_w"]=100;


    }
    if($row["dept_name"]==NULL){
        $row["dept_name"]="";
    }
    if($row["sub_name"]==NULL){
        $row["sub_name"]="";
    }
    if($row["sector_name"]==NULL){
        $row["sector_name"]="";
    }
    return $row;
}))
->pipe(new CopyColumn(array(
    "perf_sum_w1"=>"perf_sum_w",
    // "copy_of_amount"=>"amount"
)))
->pipe(new ValueMap(array(
    "perf_sum_w1" =>array(
        "{func}"=>function($value){
            // switch ($value) {
                static $i=1;
                $i++;	
                return $i;	
                
        }			
    )
)))
->pipe(new Sort(array(
    "perf_sum_w1"=>"asc",
    // "name"=>"desc"
)))
->pipe(new \koolreport\processes\Map([
    '{value}' => function ($row, $meta, $index, $mapState) {
        if(isset($row['perf_sum_w'])) {
            $row['perf_sum_w'] = $row['perf_sum_w'].'%';
        }
        return $row;
    },

]))
// ->pipe(new Limit(array($this->params['top_performing'])))
->pipe(new CalculatedColumn(array(
    "id"=>"{#}+1",
    "perf_name"=>array(
        "exp"=>function($data){
            
            $perdata = str_replace("%", "", $data["perf_sum_w"]);
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
            
           
        }),
      
)))
->pipe(new OnlyColumn(array(
    "perf_name",
    "dept_name",
    "sector_name",
    "sub_name",
    // "kpi_name",
    // "kpi_symbol",
    "perf_sum_w",
    "perf_sum_w1"
)))
->pipe(new Filter(array(
    array("perf_name","!=","rest")
)))
// ->pipe($this->dataStore('test'))

 ->pipe(new Pivot(array(
        "dimensions"=>array(
            "row"=>"perf_name,sector_name,dept_name,sub_name,perf_sum_w",
            // "column"=>"kpi_symbol,kpi_name,next_reading_date"
        ),
        'aggregates'=>array(
            'sum'=>'perf_sum_w1',
            'count'=>'perf_sum_w1'
        )

    )))
->pipe($this->dataStore("combined"));

//----------------------------
    $this->src("mysql")
            ->query("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3")
            ->pipe($this->dataStore('sector1'));

        if ($this->params["sector"] != null) {
            $this->src("mysql")
                ->query("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '_') from subtenant where parent_id = :sector_id UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('_', 50), :sector_id, '', CONCAT(id, '') from subtenant where parent_id = :sector_id) select id, name from cte order by path")
                ->params(array(":sector_id" => $this->params["sector"]))
                ->pipe(new Filter(array(
                    array("name","notContain","==> "),
                )))
                ->pipe($this->dataStore('section1'));

                /*
                 //$subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)) from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '-'), '>', s.name), s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id) select id, name from cte order by path"));
        $subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '') from subtenant where parent_id = $id and subtenant_type_id !=6 UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('', 50), $id, '', CONCAT(id, '') from subtenant where parent_id = $id and subtenant_type_id !=6) select id, name from cte order by path"));

       // $subtenants = \DB::select(\DB::raw("select id, name from subtenant where parent_id = $id and subtenant_type_id=4"));
        foreach($subtenants as $subtenant){
//            $subtenant_type_id = \DB::select(\DB::raw("select subtenant_type_id from subtenant where id=$subtenant->id"));
//            $subtenant_type_idval = $subtenant_type_id[0]->subtenant_type_id;
            //echo "in";
           $word = "==>";

            if(strpos($subtenant->name, $word) !== false) {
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
        foreach ($subtenants as $key=>$val) {
            if ($val === null || $val==='')
                unset($subtenants
[$key]);
        }
        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $subtenants
            ]);
        }
    }
                */
                
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

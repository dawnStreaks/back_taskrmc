<?php

namespace Modules\ClientApp\Reports;
class KpiValuesReport extends \koolreport\KoolReport
{
    use \koolreport\inputs\Bindable;
    use \koolreport\inputs\POSTBinding;

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
        $this->src("mysql")
            // ->query("set @kpi_cat = :kpi_category_id;")
            ->query("select kv.target_date as scheduled_date, kv.actual_value as value, kv.actual_numerator as value_numerator, kv.actual_denominator as value_denominator
            , kd.value_type, kd.numerator_name, kd.denominator_name, kd.rounding_decimals
            , kv.actual_date as value_date, notes
            , pt.id as value_explanation_id, pt.name as value_explanation_name
            from kpi_values kv, kpi_target kt, kpi_def kd, kpi_performance_type pt where
            kv.kpi_target_id = kt.id and
            kt.kpi_id = kd.id and
            pt.id = kd.value_explanation and
            kt.kpi_id = :kpi and 
            kt.mtp_id = :mtp
            order by kv.target_year, kv.target_month;")
            ->params(array(
                ":kpi" => $this->params["kpi"],
                ":mtp" => $this->params["mtp"]
            ))
            ->pipe($this->dataStore('user_details'));

        $this->src("mysql")
            ->query("select * from trans_table")
            ->pipe($this->dataStore('translation'))->requestDataSending();
        $this->src("mysql")
            ->query("select id,name from mtp")
            ->pipe($this->dataStore('mtp1'));
    }
}

<?php

namespace Modules\ClientApp\Http\Controllers;

use function foo\func;
use Illuminate\Support\Facades\DB;
use Modules\ClientApp\Dynamic;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\SubTenant;
use Modules\ClientApp\User;
use Carbon\Carbon;
use Maher\Counters\Facades\Counters;
use Maher\Counters\Models\Counter;

use Illuminate\Support\Facades\Redis;

class formGeneratorController extends Controller
{
    // protected $redis;

    function __construct()
    {
         $this->redis = Redis::connection();
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        //$groupTypes = FgForm::all();

        $PriorityType = \DB::table('fg_form')
            ->where('id', '!=', 2)
            ->where('id', '!=', 3)
            ->get();

        if ($PriorityType) {
            return response()->json([
                "code" => 200,
                "data" => $PriorityType
            ]);
        }

        return response()->json([
            "code" => 404,
            "msg" => "data not found"
        ]);
    }

    public function numberTowords($num)
    {
        $ones = array(
            1 => "first",
            2 => "second",
            3 => "third",
            4 => "four",
            5 => "five",
            6 => "six",
            7 => "seven",
            8 => "eight",
            9 => "nine",
            10 => "ten",
            11 => "eleven"
        );
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {
            if ($i < 20) {
                $rettxt .= $ones[$i] . 'TabSchema';
            }
        }
        return $rettxt;
    }

    /**
     * @param $id
     * @param $kpi_id
     * @return \Illuminate\Http\JsonResponse
     * Use: For vue form generator model create
     */
    public function getForm($id, $kpi_id)
    {
        $datas = [];
        $kpihistorycount = '';
        $kpiVals = [];
        $kpivalues = [];
        // if ($id == 1) {
        $fiscalyear = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(),fye.end_date from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1  and  fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() >= fye.end_date order by id DESC limit 1"));
        $fiscalyearstartdate = $fiscalyear[0]->start_date;
        $fiscalyearenddate = $fiscalyear[0]->end_date;

        $kpivalues = \DB::select(\DB::raw("select k.* ,d.id as kpiid,t.base_y_value, k.kpi_target_id as targetid ,d.name as kpiname,d.numerator_name as numeratorname,d.denominator_name as denominatorname, sub.name as subtenant_name,d.value_type from kpi_values k, kpi_def d, kpi_target t,subtenant sub where d.id = t.kpi_id and k.kpi_target_id = t.id and d.id=$kpi_id and sub.id = d.child_subtenant_id  and date(k.target_date) BETWEEN CAST('$fiscalyearstartdate' AS DATE) AND CAST('$fiscalyearenddate' AS DATE)"));

        $kpihistorycount = (count($kpivalues) > 0) ? count($kpivalues) : '';
        // }

        $kpiDef = \DB::select(\DB::raw("select id, name, symbol, target_determining_method, value_period as value_periodicity, value_type, number_type, rounding_decimals from kpi_def where id=$kpi_id"));

        if ($id == 3) {
            $datas = $this->kpivalueshistory($kpi_id);
            $kpihistorycount = (count($datas) > 0) ? $datas['kpihistorycount'] : 0;
            //if (isset($datas['code']) && $datas['code'] === 200) {
            if (($datas['kpivaluesactualcount'] > 0 || $kpihistorycount == 0) && ($kpiDef[0]->target_determining_method == 1)) {
                return response()->json([
                    "code" => 200,
                    "kpi_history" => true,
                ]);
            }
            //}
        }

        //$kpiDef = \DB::select(\DB::raw("select id, target_determining_method, value_period as value_periodicity, value_type, number_type, rounding_decimals from kpi_def where id=$kpi_id"));

        $mtpId = $baseFy = $prev_mtp = $base_y_value = $annual_rate_h = $min_y_value = $max_y_value = 0;
        $q1_pct = $q2_pct = $q3_pct = $q4_pct = 0;
        $y1_rate_p = $y2_rate_p = $y3_rate_p = 0;

        $y1_value = $y2_value = $y3_value = $mtp_value = $max_value = 0;
        $y1_q1_value = $y1_q2_value = $y1_q3_value = $y1_q4_value = 0;
        $y2_q1_value = $y2_q2_value = $y2_q3_value = $y2_q4_value = 0;
        $y3_q1_value = $y3_q2_value = $y3_q3_value = $y3_q4_value = 0;
        if ($kpi_id != 'null' && $id == 3) {
            $mtpdata = \DB::select(\DB::raw("select mtp.id from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1 and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));
            $mtpId = $mtpdata[0]->id;
            if ($mtpId) {
                $baseFySQL = \DB::select(\DB::raw("select (select mprev.mtp_end from mtp mprev where mprev.id = mtp.prev_mtp) base_fy, mtp.prev_mtp, mtp.kpi_margin_pct from mtp where mtp.id = $mtpId"));
                $baseFy = ($baseFySQL) ? $baseFySQL[0]->base_fy : 0;
                $prev_mtp = ($baseFySQL) ? $baseFySQL[0]->prev_mtp : 0;
                $margin_pct = ($baseFySQL) ? $baseFySQL[0]->kpi_margin_pct : 0;
            }

            if (count($kpiDef) > 0 && $id == 3) {
                $kpiVals = \DB::select(\DB::raw("select kvs.last_y_value base_y_value, kvs.y_growth_rate annual_rate_h,  kvs.q1_pct, kvs.q2_pct, kvs.q3_pct, kvs.q4_pct, kvs.max_y_value, kvs.min_y_value from kpi_values_stats kvs, kpi_target kt where
		kvs.kpi_target_id = kt.id and
		kt.kpi_id = $kpi_id and /*set kt.kpi_id as your argument for the kpi_def.id*/
		kt.mtp_id = $prev_mtp and /*set kt.mtp_id as your argument for the prev_mtp*/
		kvs.year_no = 0 ; /*0: fixed*/"));
                if (count($kpiVals) > 0) {
                    $base_y_value = ($kpiVals[0]->base_y_value) ? $kpiVals[0]->base_y_value : 0;
                    $min_y_value = ($kpiVals[0]->min_y_value) ? $kpiVals[0]->min_y_value : 0;
                    $max_y_value = ($kpiVals[0]->max_y_value) ? $kpiVals[0]->max_y_value : 0;
                    $annual_rate_h = ($kpiVals[0]->annual_rate_h) ? $kpiVals[0]->annual_rate_h : 0;
                    if ($kpiDef[0]->value_type != 1) {
                        $q1_pct = ($kpiVals[0]->q1_pct) ? $kpiVals[0]->q1_pct : 1;
                        $q2_pct = ($kpiVals[0]->q2_pct) ? $kpiVals[0]->q2_pct : 1;
                        $q3_pct = ($kpiVals[0]->q3_pct) ? $kpiVals[0]->q3_pct : 1;
                        $q4_pct = ($kpiVals[0]->q4_pct) ? $kpiVals[0]->q4_pct : 1;
                    } else {
                        $q1_pct = ($kpiVals[0]->q1_pct) ? $kpiVals[0]->q1_pct : 0;
                        $q2_pct = ($kpiVals[0]->q2_pct) ? $kpiVals[0]->q2_pct : 0;
                        $q3_pct = ($kpiVals[0]->q3_pct) ? $kpiVals[0]->q3_pct : 0;
                        $q4_pct = ($kpiVals[0]->q4_pct) ? $kpiVals[0]->q4_pct : 0;
                    }
                    $y1_rate_p = $annual_rate_h;
                    $y2_rate_p = $annual_rate_h;
                    $y3_rate_p = $annual_rate_h;
                }
            }
        }

        $userData = \DB::select(\DB::raw("select id, db_column from fg_control where is_arg='1'"));
        $userData1 = \DB::select(\DB::raw("select id, db_column, expression from fg_control where  expression like 'sql%' and is_arg=0"));

        $dataValues = $withReplace = '';
        if (count($userData) > 0 && count($userData1) > 0) {
            $arrayArgs = [];
            $arrayArgsNo = [];
            $i = 0;
            foreach ($userData as $k => $v) {
                $checkArg = '{{' . $v->id . '}}';
                $replaceArg = '[' . $v->db_column . ']';
                $arrayArgs[$checkArg] = $replaceArg;
                $arrayArgsNo[$i] = $v->db_column;
                //$arra[$i][$v->db_column] = $arra;
                $i++;
            }
            $search = array_keys($arrayArgs);
            $replace = array_values($arrayArgs);
            $dataArray = [];
            $dataReplaceWith = [];
            $j = 0;
            foreach ($userData1 as $key => $val) {
                foreach ($search as $url) {
                    //if (strstr($string, $url)) { // mine version
                    if (strpos($val->expression, $url) !== FALSE) { // Yoshi version
                        $dataReplaceWith[$j] = $val->db_column;
                        $vals = str_replace($search, $replace, $val->expression);
                        //if ($vals) {
                        $dataArray[$j]['link'] = $val->db_column;

                        $dataArray[$j]['expression'] = $vals;
                        //$val->expression = $vals;
                        //}
                        $j++;
                    }
                }
            }

            $replaceWith = array_unique($dataReplaceWith, SORT_REGULAR);

            $dataValues = array_unique($dataArray, SORT_REGULAR);

            $withReplace = array_combine(array_values($arrayArgsNo), $replaceWith);

            $dataValues = array_values($dataValues);
            foreach ($withReplace as $key => $val) {
                foreach ($dataValues as $keys => $values) {
                    if ($val == $values['link']) {
                        $dataValues[$keys]['main'] = $key;
                    }
                }
            }
        }
        $PriorityType = \DB::table('fg_form')
            ->join('fg_section', 'fg_section.form_id', '=', 'fg_form.id')
            ->join('fg_control', 'fg_control.section_id', '=', 'fg_section.id')
            ->join('fg_control_type', 'fg_control_type.id', '=', 'fg_control.type_id')
            ->select('fg_form.label as title', 'fg_form.is_linked', 'fg_form.next_form_id', 'fg_form.next_form_arg', 'fg_form.type', 'fg_control.styleClasses', 'fg_form.db_table', 'fg_section.label as section_title', 'fg_section.show_label', 'fg_section.sort_no', 'fg_control.label as input_label', 'fg_control.sort_no as input_sort', 'fg_control.type_id', 'fg_control.input_type', 'fg_control_type.name as field_type', 'fg_control.is_mandatory', 'fg_control.db_column', 'fg_control.expression', 'fg_control.is_visible', 'fg_control.mask')
            ->where('fg_form.id', $id)
            ->where('fg_control.is_visible', 1)
            //->orderBy('fg_section.id', 'fg_section.sort_no')
            ->orderByRaw('fg_section.sort_no, input_sort ASC')
            ->get();

        if (count($PriorityType) > 0) {
            $categories = array();
            foreach ($PriorityType as $data) {
                $categories[$data->sort_no][] = $data;
            }

            $db_name = $data->db_table;
            $All = array();
            $type = '';
            $next_form_id = '';
            $next_form_arg = '';
            foreach ($categories as $key => $val) {
                $vals = [];
                $i = 0;
                $j = 0;
                // var_dump($val);
                foreach ($val as $item) {
                    $type = $item->type;
                    $next_form_id = $item->next_form_id;
                    $next_form_arg = $item->next_form_arg;
                    $is_linked = $item->is_linked;
                    //$All[$this->numberTowords($key)]['groups']['legend'] = $item->title;
                    $vals['groups'][$j]['legends'] = $item->section_title;
                    $vals['groups'][$j]['fields'][$i]['type'] = $item->field_type;
                    $vals['groups'][$j]['fields'][$i]['inputType'] = $item->input_type;

                    if ($item->field_type == 'masked')
                        $vals['groups'][$j]['fields'][$i]['mask'] = "(99) 999-9999";
                    //$vals['inputType'] = $val;
                    /*if($item->field_type == 'select') {
                        $vals['groups'][$j]['fields'][$i]['values'] = [];
                    }*/
                    /* if($item->field_type == 'radios') {
                         $vals['groups'][$j]['fields'][$i]['values'] = ["Male", "Female"];
                     }*/
                    $vals['groups'][$j]['fields'][$i]['label'] = $item->input_label;
                    $vals['groups'][$j]['fields'][$i]['model'] = $item->db_column;
                    $vals['groups'][$j]['fields'][$i]['placeholder'] = $item->db_column;
                    $vals['groups'][$j]['fields'][$i]['selectOptions']['noneSelectedText'] = "please_select";
                    if (isset($item->expression)) {
                        $exp = explode(":", $item->expression);
                        if ($exp[0] == 'txt') {
                            $val = explode("=", $exp[1]);
                            $option = [];
                            if ($item->db_column == 'gender') {
                                foreach (explode(",", $val[1]) as $ke => $val) {
                                    $option[$ke]['value'] = $ke;
                                    $option[$ke]['name'] = $val;
                                }
                            } elseif ($item->db_column == 'scope_table') {
                                $option = explode(",", $val[1]);
                            } else {
                                foreach (explode(",", $val[1]) as $ke => $val) {
                                    $opVlaus = explode("->", $val);
                                    if (count($opVlaus) > 1) {
                                        $option[$ke]['id'] = trim($opVlaus[0]);
                                        $option[$ke]['name'] = trim($opVlaus[1]);
                                    } else {
                                        $option[$ke]['id'] = $ke;
                                        $option[$ke]['name'] = $val;
                                    }
                                }
                            }
                            //var_dump(explode(",", $val[1]));
                            /* $option = [];
                             $optionCount = explode(",", $val[1]);

                             if (count($optionCount) > 0) {
                                 if ($item->db_column == 'gender') {
                                     foreach (explode(",", $val[1]) as $key => $va) {
                                         $option[$key]['value'] = $key;
                                         $option[$key]['name'] = $va;
                                     }
                                 } else {
                                     foreach (explode(",", $val[1]) as $key => $va) {
                                         $option[$key]['id'] = $key;
                                         $option[$key]['name'] = $va;
                                     }
                                 }
                                 // $vals['groups'][$j]['fields'][$i]['values'] = $option;
                             } else {
                                 $option = explode(",", $val[1]);
                             }*/

                            $vals['groups'][$j]['fields'][$i]['values'] = $option;//explode(",", $val[1]);
                        } elseif ($exp[0] == 'sql') {
                            $val = explode("==", $exp[1]);

                            if ($item->db_column == 'address_city_id') {
                                $vals['groups'][$j]['fields'][$i]['values'] = [];
                            } elseif ($item->db_column == 'child_subtenant_id') {
                                $vals['groups'][$j]['fields'][$i]['values'] = [];
                            } elseif ($item->db_column == 'scope_id') {
                                $vals['groups'][$j]['fields'][$i]['values'] = [];
                            } elseif ($item->db_column == 'base_fy') {
                                $result = str_replace("base_fy", $baseFy, $val[1]);
                                $baseYear = \DB::select(\DB::raw($result));
                                $vals['groups'][$j]['fields'][$i]['values'] = $baseYear;
                                $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                            } /*elseif ($item->db_column == 'user_of_auditing' || $item->db_column == 'user_of_contact') {
                                $users = $this->user->allowedUsers();
                                $vals['groups'][$j]['fields'][$i]['values'] = $users;
                            }*/ else {
                                $country = \DB::select(\DB::raw($val[1]));
                                $vals['groups'][$j]['fields'][$i]['values'] = $country;
                            }
                            $vals['groups'][$j]['fields'][$i]['sql'] = $val[1];
                            //$vals['groups'][$j]['fields'][$i]['onChanged'] = 'test()';
                        } else if ($exp[0] == 'formula') {

                            if (count($kpiVals) > 0) {
                                $ma = $exp[1];
                                $result = eval("return " . $ma . ";");

                                if ($item->db_column == 'y1_value' && count($kpiDef) > 0) {
                                    $y1_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                }
                                if ($item->db_column == 'y2_value' && count($kpiDef) > 0) {
                                    $y2_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);;
                                }
                                if ($item->db_column == 'y3_value' && count($kpiDef) > 0) {
                                    $y3_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                }
                                if ($item->db_column == 'mtp_value' && count($kpiDef) > 0) {
                                    if ($kpiDef[0]->value_type == 1) {
                                        $mtp_value = $y1_value + $y2_value + $y3_value;
                                        $mtp_value = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $mtp_value * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $mtp_value);
                                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $mtp_value * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $mtp_value);
                                    }
                                }

                                if (($item->db_column == 'y1_q1_value') && count($kpiDef) > 0) {
                                    //echo $item->expression;
                                    $y1_q1_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }

                                if (($item->db_column == 'y1_q2_value') && count($kpiDef) > 0) {
                                    $y1_q2_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }

                                if (($item->db_column == 'y1_q3_value') && count($kpiDef) > 0) {
                                    $y1_q3_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }

                                if (($item->db_column == 'y1_q4_value') && count($kpiDef) > 0) {
                                    $y1_q4_value = $result;
                                    if ($kpiDef[0]->value_type == 1) {
                                        $result = $y1_value - (sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y1_q1_value) + sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y1_q2_value) + sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y1_q3_value));
                                    }

                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }

                                if (($item->db_column == 'y2_q1_value') && count($kpiDef) > 0) {
                                    $y2_q1_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }
                                if (($item->db_column == 'y2_q2_value') && count($kpiDef) > 0) {
                                    $y2_q2_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }
                                if (($item->db_column == 'y2_q3_value') && count($kpiDef) > 0) {
                                    $y2_q3_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }

                                if (($item->db_column == 'y2_q4_value') && count($kpiDef) > 0) {
                                    $y2_q4_value = $result;
                                    if ($kpiDef[0]->value_type == 1) {
                                        $result = $y2_value - (sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y2_q1_value) + sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y2_q2_value) + sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y2_q3_value));
                                    }
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;

                                }

                                if (($item->db_column == 'y3_q1_value') && count($kpiDef) > 0) {
                                    $y3_q1_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }
                                if (($item->db_column == 'y3_q2_value') && count($kpiDef) > 0) {
                                    $y3_q2_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }
                                if (($item->db_column == 'y3_q3_value') && count($kpiDef) > 0) {
                                    $y3_q3_value = $result;
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                                }

                                if (($item->db_column == 'y3_q4_value') && count($kpiDef) > 0) {
                                    $y3_q4_value = $result;
                                    if ($kpiDef[0]->value_type == 1) {
                                        $result = $y3_value - (sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y3_q1_value) + sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y3_q2_value) + sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $y3_q3_value));
                                    }
                                    $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result * 100) : sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $result);
                                    $vals['groups'][$j]['fields'][$i]['disabled'] = true;

                                }
                            }
                        }
                    }

                    if ($item->db_column == 'symbol') {
                        $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                    }
                    if ($item->db_column == 'min_value') {
                        $vals['groups'][$j]['fields'][$i]['attributes']['input']['data-toggle'] = 'tooltip';
                        $vals['groups'][$j]['fields'][$i]['attributes']['input']['title'] = 'tooltip: kpi target min_value explanation and importance';
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = 0;
                    }
                    if ($item->db_column == 'max_value') {

                        $checkArray = [$base_y_value, $max_y_value, $min_y_value, $y1_value, $y2_value, $y3_value];
                        $max_value = ($max_value < max($checkArray) ? max($checkArray) : $max_value);
                        /*$max_value = (($y3_value > $y1_value && $y3_value > $y2_value && $y3_value > $base_y_value && $y3_value >= $max_value) ? $y3_value :
                            (($y2_value > $y1_value && $y2_value > $y3_value && $y2_value > $base_y_value && $y2_value >= $max_value) ? $y2_value : (($y1_value > $y2_value && $y1_value > $y3_value && $y1_value > $base_y_value && $y1_value >= $max_value) ? $y1_value : (($base_y_value > $y1_value && $base_y_value > $y2_value && $base_y_value > $y3_value && $base_y_value >= $max_value) ? $base_y_value : $max_value))));*/

                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $max_value;
                        $vals['groups'][$j]['fields'][$i]['attributes']['input']['data-toggle'] = 'tooltip';
                        $vals['groups'][$j]['fields'][$i]['attributes']['input']['title'] = 'tooltip: kpi target max_value explanation and importance';
                    }

                    if ($item->db_column == 'number_type') {
                        $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                    }
                    if (in_array($item->db_column, ['data_name', 'data_subtenant_id', 'data_phone_internal', 'data_email', 'auditing_name', 'auditing_subtenant_id', 'auditing_phone_internal', 'auditing_email',
                        'contact_name', 'contact_subtenant_id', 'contact_phone_internal', 'contact_email',
                        'coordination_name', 'coordination_subtenant_id', 'coordination_phone_internal', 'coordination_email'])) {
                        $vals['groups'][$j]['fields'][$i]['values'] = [];
                    }

                    /*if (in_array($item->db_column, ['norm_value', 'norm_date', 'region_origin'])) {
                        $vals['groups'][$j]['fields'][$i]['values'] = [];
                    }*/

                    if ($item->db_column === 'input_with_button') {
                        $vals['groups'][$j]['fields'][$i]['buttons'][] = [
                            "classes" => "btn-location",
                            "label" => "Model",
                        ];
                    }

                    /*if (in_array($item->db_column, ['y1_value', 'y2_value', 'y3_value', 'mtp_value', 'y1_q1_value', 'y1_q2_value', 'y1_q3_value', 'y1_q4_value', 'y2_q1_value', 'y2_q2_value', 'y2_q3_value', 'y2_q4_value', 'y3_q1_value', 'y3_q2_value', 'y3_q3_value', 'y3_q4_value']) && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                    }*/

                    if ($item->db_column == 'target_determining_method' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $kpiDef[0]->target_determining_method;
                    }

                    if ($item->db_column == 'value_periodicity' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $kpiDef[0]->value_periodicity;;
                    }
                    if ($item->db_column == 'value_period' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $kpiDef[0]->value_periodicity;
                    }

                    if ($item->db_column == 'mtp_id' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $mtpId;
                    }

                    if ($item->db_column == 'base_fy' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $baseFy;
                    }

                    if ($item->db_column == 'margin_pct' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $margin_pct * 100;
                    }

                    if ($item->db_column == 'mtp_value' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = $mtp_value;
                    }

                    if ($item->db_column == 'base_y_value' && count($kpiDef) > 0) {
                        if ($kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                        }
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $base_y_value * 100) : $base_y_value;
                    }
                    if ($item->db_column == 'min_y_value' && count($kpiDef) > 0) {
                        if ($kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                        }
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $min_y_value * 100) : $min_y_value;
                    }
                    if ($item->db_column == 'max_y_value' && count($kpiDef) > 0) {
                        if ($kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                        }
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $max_y_value * 100) : $max_y_value;
                    }

                    if ($item->db_column == 'analysis_resource' && count($kpiDef) > 0) {
                        if ($kpiDef[0]->target_determining_method === 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                        }
                    }

                    if ($item->db_column == 'annual_rate_h' && count($kpiDef) > 0) {
                        if ($kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                        }
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.4f", $annual_rate_h * 100);
                    }

                    if ($item->db_column == 'y1_rate_p' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.4f", $y1_rate_p * 100);
                    }
                    if ($item->db_column == 'y2_rate_p' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.4f", $y2_rate_p * 100);
                    }
                    if ($item->db_column == 'y3_rate_p' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.4f", $y3_rate_p * 100);
                    }

                    if (($item->db_column == 'numerator_name' || $item->db_column == 'denominator_name') && count($kpiDef) > 0) {
                        if ($kpiDef[0]->value_type == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                        }
                    }
                    if (($item->db_column == 'rounding_decimals') && count($kpiDef) > 0) {
                        if ($kpiDef[0]->value_type == 1) {
                            $vals['groups'][$j]['fields'][$i]['readonly'] = true;
                        }
                    }
                    if ($item->db_column == 'q1_pct' && count($kpiDef) > 0) {
                        //echo $kpiDef[0]->value_type.'==='.$kpiDef[0]->target_determining_method;
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q1_pct * 100);
                        if ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q1_pct * 100);
                        } elseif ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method != 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q1_pct * 100);
                        } elseif ($kpiDef[0]->value_type <> 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", 1 * 100);
                        }
                    }

                    if ($item->db_column == 'q2_pct' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q2_pct * 100);
                        if ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q2_pct * 100);
                        } elseif ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method != 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q2_pct * 100);
                        } elseif ($kpiDef[0]->value_type <> 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", 1 * 100);
                        }
                    }

                    if ($item->db_column == 'q3_pct' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q3_pct * 100);
                        if ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q3_pct * 100);
                        } elseif ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method != 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q3_pct * 100);
                        } elseif ($kpiDef[0]->value_type <> 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", 1 * 100);;
                        }
                    }

                    if ($item->db_column == 'q4_pct' && count($kpiDef) > 0) {
                        $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", $q4_pct * 100);
                        if ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method == 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%.2f", $q4_pct * 100) : sprintf("%.2f", (100 - ($q1_pct + $q2_pct + $q3_pct) * 100));
                        } elseif ($kpiDef[0]->value_type == 1 && $kpiDef[0]->target_determining_method != 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = false;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = ($kpiDef[0]->value_type == 2) ? sprintf("%.2f", $q4_pct * 100) : sprintf("%.2f", (100 - ($q1_pct + $q2_pct + $q3_pct) * 100));
                        } elseif ($kpiDef[0]->value_type <> 1) {
                            $vals['groups'][$j]['fields'][$i]['disabled'] = true;
                            $vals['groups'][$j]['fields'][$i]['defaultVal'] = sprintf("%.2f", 1 * 100);
                        }
                    }


                    $vals['groups'][$j]['fields'][$i]['styleClasses'] = $item->styleClasses;

                    if (in_array($item->db_column, ['expected_activation_date', 'base_from_date', 'base_to_date', 'base_from_date1', 'target_start_date'])) {
                        $vals['groups'][$j]['fields'][$i]['dateTimePickerOptions'] = [
                            "format" => "YYYY-MM-DD"
                        ];
                        $vals['groups'][$j]['fields'][$i]['format'] = "YYYY-MM-DD";
                    }

                    $vals['groups'][$j]['fields'][$i]['validator'] = array("required");
                    $vals['groups'][$j]['fields'][$i]['default'] = '';
                    $vals['groups'][$j]['fields'][$i]['required'] = (($item->is_mandatory) ? true : false);
                    $i++;
                }
                $ftype = [];
                $ftype1 = [];
                if ($item->type == 'w') {
                    $All[$key] = $vals;
                } else {
                    $ftype1['type'] = "submit";
                    $ftype1['buttonText'] = "cancel";

                    $ftype['type'] = "submit";
                    $ftype['buttonText'] = "submit";
                    $ftype['validateBeforeSubmit'] = true;
                    //$ftype['fieldClasses'] = 'DSemo';
                    // $vals[] = $ftype;
                    array_push($vals['groups'][0]['fields'], $ftype1);
                    array_push($vals['groups'][0]['fields'], $ftype);
                    $All[1] = $vals;
                }
                //$All[$key] = $vals;
                $j++;
            }
            return response()->json([
                "code" => 200,
                "data" => (array)$All,
                'propTitle' => (
                ($db_name == 'kpi_def') ? "kpi definition" :
                    (($db_name == 'kpi_target') ? "add target wizard" : 'Wizard')
                ),
                'propSubTitle' => (
                ($db_name == 'kpi_def') ? "kpi wizard title" :
                    (($db_name == 'kpi_target') ? "target wizard title" : 'wizard title')
                ),
                'db_name' => $db_name,
                "type" => $type,
                "next_form_id" => $next_form_id,
                "next_form_arg" => $next_form_arg,
                "is_linked" => $is_linked,
                "dataValues" => $dataValues,
                "withReplace" => $withReplace,
                "value_type" => (count($kpiDef) > 0) ? $kpiDef[0]->value_type : '',
                "rounding_decimals" => (count($kpiDef) > 0) ? $kpiDef[0]->rounding_decimals : '',
                "kpi_name" => (count($kpiDef) > 0) ? $kpiDef[0]->name : '',
                "kpi_symbol" => (count($kpiDef) > 0) ? $kpiDef[0]->symbol : '',
                "kpihistorycount" => $kpihistorycount,
            ]);
        }
    }

    /**
     * @param $formId
     * @return \Illuminate\Http\JsonResponse
     * Get Data
     */
    public function gettabledata($formId)
    {
        $Tabledataarray = [];
        $data = \DB::select(\DB::raw("select db_table from fg_form where id=$formId"));

        $Tabledata = \DB::table($data[0]->db_table)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($Tabledata as $key => $Tabledatas) {
            if (isset($Tabledatas->region_of_origin)) {
                if ($Tabledatas->region_of_origin == 0) {
                    $Tabledatas->region_of_origin = "National";
                }
                if ($Tabledatas->region_of_origin == 1) {
                    $Tabledatas->region_of_origin = "Gulf";
                }
                if ($Tabledatas->region_of_origin == 2) {
                    $Tabledatas->region_of_origin = "Arabic";
                }
                if ($Tabledatas->region_of_origin == 3) {
                    $Tabledatas->region_of_origin = "Inter National";
                }
            }

            $fiscalyear = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date;"));
            foreach ($fiscalyear as $key => $fiscalyears) {
                $Tabledatas->target_start_date = $fiscalyears->start_date;
            }
            $Tabledataarray[] = $Tabledatas;

        }
        return response()->json(
            $Tabledataarray
        );

    }

    /**
     * @param $tableName
     * @return \Illuminate\Http\JsonResponse
     * Get Data by Name
     */
    public function gettabledatabyName($tableName)
    {
        $Tabledataarray = [];
        $Tabledata = \DB::table($tableName)
            ->orderBy('id', 'DESC')
            ->get();
        foreach ($Tabledata as $key => $Tabledatas) {
            if (isset($Tabledatas->region_of_origin)) {
                if ($Tabledatas->region_of_origin == 0) {
                    $Tabledatas->region_of_origin = "National";
                }
                if ($Tabledatas->region_of_origin == 1) {
                    $Tabledatas->region_of_origin = "Gulf";
                }
                if ($Tabledatas->region_of_origin == 2) {
                    $Tabledatas->region_of_origin = "Arabic";
                }
                if ($Tabledatas->region_of_origin == 3) {
                    $Tabledatas->region_of_origin = "Inter National";
                }
            }

            $Tabledataarray[] = $Tabledatas;

        }
        return response()->json(
            $Tabledataarray
        );

    }

    /**
     * @param $formId
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * Get Data By Id
     */
    public function tabledatabyId($formId, $id)
    {
        $Tabledataarray = [];
        $data = \DB::select(\DB::raw("select db_table from fg_form where id=$formId"));
        $dbname = $data[0]->db_table;

        $subTenants = $process_obj = '';
        if ($dbname == 'benchmark_kpi_rel') {
            $Tabledata = \DB::select(\DB::raw("select rel.benchmarket_id, def.region_of_origin as region_origin, norm.norm_value, norm.norm_date from benchmark_kpi_rel rel INNER JOIN benchmark_def def ON rel.benchmarket_id = def.id INNER JOIN benchmark_norm norm on def.id = norm.benchmark_id where rel.kpi_id='" . $id . "'"));

        } elseif ($dbname == 'kpi_target') {
            $mtpId = $baseFy = $prev_mtp = $base_y_value = $annual_rate_h = $q1_pct = $q2_pct = $q3_pct = $q4_pct = $min_y_value = $max_y_value = 0;

            $mtpdata = \DB::select(\DB::raw("select mtp.id from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1 and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));
            $mtpId = $mtpdata[0]->id;

            $kpiDef = \DB::select(\DB::raw("select id, target_determining_method, value_period as value_periodicity, value_type, rounding_decimals from kpi_def where id=$id"));

            if ($mtpId) {
                $baseFySQL = \DB::select(\DB::raw("select (select mprev.mtp_end from mtp mprev where mprev.id = mtp.prev_mtp) base_fy, mtp.prev_mtp, mtp.kpi_margin_pct from mtp where mtp.id = $mtpId"));
                $prev_mtp = ($baseFySQL) ? $baseFySQL[0]->prev_mtp : 0;
            }

            if (count($kpiDef) > 0) {
                $kpiVals = \DB::select(\DB::raw("select kvs.last_y_value base_y_value, kvs.y_growth_rate annual_rate_h,  kvs.q1_pct, kvs.q2_pct, kvs.q3_pct, kvs.q4_pct, kvs.max_y_value, kvs.min_y_value from kpi_values_stats kvs, kpi_target kt where
		kvs.kpi_target_id = kt.id and
		kt.kpi_id = $id and /*set kt.kpi_id as your argument for the kpi_def.id*/
		kt.mtp_id = $prev_mtp and /*set kt.mtp_id as your argument for the prev_mtp*/
		kvs.year_no = 0 ; /*0: fixed*/"));
                if (count($kpiVals) > 0) {
                    $min_y_value = ($kpiVals[0]->min_y_value) ? $kpiVals[0]->min_y_value : 0;
                    $max_y_value = ($kpiVals[0]->max_y_value) ? $kpiVals[0]->max_y_value : 0;
                }
            }

            $Tabledata = \DB::select(\DB::raw("select * from $dbname where kpi_id=$id and mtp_id=$mtpId"));
            if (count($kpiDef) > 0 && count($Tabledata) > 0) {
                $Tabledata[0]->value_type = $kpiDef[0]->value_type;

                $Tabledata[0]->base_y_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->base_y_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->base_y_value));

                $Tabledata[0]->max_y_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $max_y_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $max_y_value));
                $Tabledata[0]->min_y_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $min_y_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $min_y_value));


                $Tabledata[0]->annual_rate_h = sprintf("%.4f", $Tabledata[0]->annual_rate_h * 100);
                $Tabledata[0]->margin_pct = sprintf("%.2f", $Tabledata[0]->margin_pct * 100);
                $Tabledata[0]->y1_rate_p = sprintf("%.4f", $Tabledata[0]->y1_rate_p * 100);
                $Tabledata[0]->y2_rate_p = sprintf("%.4f", $Tabledata[0]->y2_rate_p * 100);
                $Tabledata[0]->y3_rate_p = sprintf("%.4f", $Tabledata[0]->y3_rate_p * 100);
                $Tabledata[0]->q1_pct = ($kpiDef[0]->value_type == 2) ? sprintf("%.2f", $Tabledata[0]->q1_pct * 100) : sprintf("%.2f", $Tabledata[0]->q1_pct * 100);
                $Tabledata[0]->q2_pct = ($kpiDef[0]->value_type == 2) ? sprintf("%.2f", $Tabledata[0]->q2_pct * 100) : sprintf("%.2f", $Tabledata[0]->q2_pct * 100);
                $Tabledata[0]->q3_pct = ($kpiDef[0]->value_type == 2) ? sprintf("%.2f", $Tabledata[0]->q3_pct * 100) : sprintf("%.2f", $Tabledata[0]->q3_pct * 100);
                $Tabledata[0]->q4_pct = ($kpiDef[0]->value_type == 2) ? sprintf("%.2f", $Tabledata[0]->q4_pct * 100) : sprintf("%.2f", $Tabledata[0]->q4_pct * 100);


                $Tabledata[0]->min_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->min_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->min_value));
                $Tabledata[0]->max_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->max_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->max_value));

                $Tabledata[0]->y1_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_value));

                $Tabledata[0]->y2_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_value));

                $Tabledata[0]->y3_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_value));

                $Tabledata[0]->y1_q1_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q1_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q1_value));
                $Tabledata[0]->y1_q2_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q2_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q2_value));
                $Tabledata[0]->y1_q3_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q3_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q3_value));
                $Tabledata[0]->y1_q4_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q4_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y1_q4_value));


                $Tabledata[0]->y2_q1_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q1_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q1_value));
                $Tabledata[0]->y2_q2_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q2_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q2_value));
                $Tabledata[0]->y2_q3_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q3_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q3_value));
                $Tabledata[0]->y2_q4_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q4_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y2_q4_value));

                $Tabledata[0]->y3_q1_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q1_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q1_value));
                $Tabledata[0]->y3_q2_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q2_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q2_value));
                $Tabledata[0]->y3_q3_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q3_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q3_value));
                $Tabledata[0]->y3_q4_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q4_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->y3_q4_value));

                $Tabledata[0]->mtp_value = ($kpiDef[0]->value_type == 2) ? number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->mtp_value * 100)) : number_format(sprintf("%." . ($kpiDef[0]->rounding_decimals) . "f", $Tabledata[0]->mtp_value));

            }

        } elseif ($dbname == 'kpi_def') {
            $Tabledata = \DB::select(\DB::raw("select * from $dbname where id=$id"));

            if (isset($Tabledata[0]->user_of_data)) {
                $userData = \DB::select(\DB::raw("select users.id, users.name, email,phone_internal,subtenant.name as subtenantname from users inner JOIN subtenant on users.subtenant_id = subtenant.id where users.id='" . $Tabledata[0]->user_of_data . "'"));

                foreach ($userData as $key => $val) {
                    if (isset($Tabledata[0]->user_of_data)) {
                        $Tabledata[0]->data_name = $val->name;
                        $Tabledata[0]->data_email = $val->email;
                        $Tabledata[0]->data_subtenant_id = $val->subtenantname;
                        $Tabledata[0]->data_phone_internal = $val->phone_internal;
                    }
                }
            }
            if (isset($Tabledata[0]->user_of_auditing)) {
                $userData = \DB::select(\DB::raw("select users.id, users.name, email,phone_internal,subtenant.name as subtenantname from users inner JOIN subtenant on users.subtenant_id = subtenant.id where users.id='" . $Tabledata[0]->user_of_auditing . "'"));
                foreach ($userData as $key => $val) {
                    if (isset($Tabledata[0]->user_of_auditing)) {
                        $Tabledata[0]->auditing_name = $val->name;
                        $Tabledata[0]->auditing_email = $val->email;
                        $Tabledata[0]->auditing_subtenant_id = $val->subtenantname;
                        $Tabledata[0]->auditing_phone_internal = $val->phone_internal;
                    }
                }
            }

            if (isset($Tabledata[0]->user_of_contact)) {
                $userData = \DB::select(\DB::raw("select users.id, users.name, email,phone_internal,subtenant.name as subtenantname from users inner JOIN subtenant on users.subtenant_id = subtenant.id where users.id='" . $Tabledata[0]->user_of_contact . "'"));
                foreach ($userData as $key => $val) {
                    if (isset($Tabledata[0]->user_of_contact)) {
                        $Tabledata[0]->contact_name = $val->name;
                        $Tabledata[0]->contact_email = $val->email;
                        $Tabledata[0]->contact_subtenant_id = $val->subtenantname;
                        $Tabledata[0]->contact_phone_internal = $val->phone_internal;
                    }
                }
            }

            if (isset($Tabledata[0]->user_of_coordination)) {
                $userData = \DB::select(\DB::raw("select users.id, users.name, email,phone_internal,subtenant.name as subtenantname from users inner JOIN subtenant on users.subtenant_id = subtenant.id where users.id='" . $Tabledata[0]->user_of_coordination . "'"));
                foreach ($userData as $key => $val) {
                    if (isset($Tabledata[0]->user_of_coordination)) {
                        $Tabledata[0]->coordination_name = $val->name;
                        $Tabledata[0]->coordination_email = $val->email;
                        $Tabledata[0]->coordination_subtenant_id = $val->subtenantname;
                        $Tabledata[0]->coordination_phone_internal = $val->phone_internal;
                    }
                }
            }
            $sql = "WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)) from subtenant where parent_id = '" . $Tabledata[0]->subtenant_id . "' UNION ALL select s.id, concat(CONCAT(c.level, '-'), '>', s.name), s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id) select id, name from cte order by path";
            $tenants = \DB::select(\DB::raw($sql));
            if ($tenants) {
                $subTenants = $tenants;
            }

            $sql2 = "select p.id, p.name from process_def p, process_subtenant_rel r1 where '" . $Tabledata[0]->scope_table . "' = 'process_def' and r1.process_id = p.id and r1.subtenant_id = '" . $Tabledata[0]->child_subtenant_id . "' union select o.id, o.name from objective o, org_objectives r2 where '" . $Tabledata[0]->scope_table . "' = 'objective' and r2.objective_id = o.id and r2.tenant_id = '" . env('TENANT_ID') . "' and r2.subtenant_id = '" . $Tabledata[0]->child_subtenant_id . "' or '" . $Tabledata[0]->child_subtenant_id . "' = null";
            $tenantsSS = \DB::select(\DB::raw($sql2));
            if ($tenants) {
                $process_obj = $tenantsSS;
            }

        } else {
            $Tabledata = \DB::select(\DB::raw("select * from $dbname where id=$id"));
        }

        foreach ($Tabledata as $key => $Tabledatas) {

            $Tabledataarray[] = $Tabledatas;
        }

        if ($subTenants) {
            $Tabledataarray['subTenants'] = $subTenants;
        }
        if ($process_obj) {
            $Tabledataarray['process_obj'] = $process_obj;
        }
        return response()->json(
            $Tabledataarray
        );

    }

    /**
     * @param $formId
     * @param $id
     * @param $value
     * @return \Illuminate\Http\JsonResponse
     */
    public function tableeditadataid($formId, $id, $value)
    {
        $data = \DB::select(\DB::raw("select db_table from fg_form where id=$formId"));
        $dbname = $data[0]->db_table;
        $Tabledataresult = \DB::select(\DB::raw("update $dbname set name='$value' where id=$id"));

        return response()->json(
            $Tabledataresult
        );
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
    public function store(Request $request)
    {

        $valType = $request['value_type'];
        //var_dump($request->all());
        //die;
        //DB::enableQueryLog(); // Enable query log
        $table = $request->get('db_name');

        unset($request['db_name']);
        unset($request['data_name']);
        unset($request['data_subtenant_id']);
        unset($request['data_phone_internal']);
        unset($request['data_email']);

        unset($request['auditing_email']);
        unset($request['auditing_name']);
        unset($request['auditing_subtenant_id']);
        unset($request['auditing_phone_internal']);

        unset($request['contact_name']);
        unset($request['contact_subtenant_id']);
        unset($request['contact_phone_internal']);
        unset($request['contact_email']);

        unset($request['coordination_name']);
        unset($request['coordination_subtenant_id']);
        unset($request['coordination_phone_internal']);
        unset($request['coordination_email']);
        //unset($request['subtenant_id']);

        unset($request['norm_date']);
        unset($request['norm_value']);
        unset($request['region_origin']);
        unset($request['max_y_value']);
        unset($request['min_y_value']);

        /*if (isset($request['child_subtenant_id'])) {
            $request['subtenant_id'] = $request['child_subtenant_id'];
        }
        unset($request['child_subtenant_id']);*/

        if ($table == 'kpi_def') {
            $request['created_by'] = auth()->user()->id;
            if (empty($request->id) || !isset($request->id) || $request->id === 0) {
                $getValCount = \DB::select(\DB::raw("select sub.id, sub.name, concat('K', sub.code) as kpi_symbol_prefix from subtenant sub where sub.id = '" . $request['subtenant_id'] . "'"));

                if (count($getValCount) > 0 && $getValCount[0]->kpi_symbol_prefix != null) {
                    $checkCounter = \DB::select(\DB::raw("select * from counters where `key` = '" . $getValCount[0]->kpi_symbol_prefix . "'"));
                    if (count($checkCounter) > 0) {
                        $count = Counters::increment($getValCount[0]->kpi_symbol_prefix);
                        $counter = Counters::getValue($getValCount[0]->kpi_symbol_prefix);
                    } else {
                        Counter::create([
                            'key' => $getValCount[0]->kpi_symbol_prefix,
                            'name' => 'kpi_symbol_counter',
                            'initial_value' => 0,
                            'step' => 1
                        ]);
                        $count = Counters::increment($getValCount[0]->kpi_symbol_prefix);
                        $counter = Counters::getValue($getValCount[0]->kpi_symbol_prefix);
                    }

                }
                $request['symbol'] = $getValCount[0]->kpi_symbol_prefix . '-' . sprintf("%04d", $counter);
            }
        }
        /* if (isset($request['status'])) {
             $request['created_by'] = auth()->user()->id;
         }
         if (isset($request['created_by'])) {
             $request['created_by'] = auth()->user()->id;
         }*/

        if (!isset($request['tenant_id'])) {
            unset($request['tenant_id']);
        }

        if ($table != 'kpi_def') {
            unset($request['max_val']);
            unset($request['min_val']);
            unset($request['importance']);
            unset($request['value_type']);
            unset($request['value_period']);
            unset($request['number_type']);
            unset($request['rounding_decimals']);
        }

        $tableInArray = [''];
        //echo $request['kpi_id'];
        if ($request['kpi_id'] == 0) {
            unset($request['kpi_id']);
        }

        if ($table == 'kpi_def') {
            if ($request['status'] == 2) {


                $request['status'] = 0;
                //var_dump($request->get('status'));
            }
            unset($request['kpi_id']);
        }

        if ($table != 'kpi_target' && $table != 'benchmark_kpi_rel') {
            unset($request['kpi_id']);
        }

        /*if ($table != 'benchmark_kpi_rel') {
            unset($request['kpi_id']);
        }*/

        if ($table != 'kpi_target') {
            unset($request['mtp_id']);
            unset($request['mtp_id']);
            unset($request['base_fy']);

            unset($request['base_y_value']);
            unset($request['annual_rate_h']);
            unset($request['base_fy']);
            unset($request['base_y_value']);
            unset($request['annual_rate_h']);
            unset($request['y1_value']);
            unset($request['y2_value']);
            unset($request['y3_value']);

            unset($request['y1_rate_p']);
            unset($request['y2_rate_p']);
            unset($request['y3_rate_p']);
            unset($request['y1_q1_value']);
            unset($request['y1_q2_value']);
            unset($request['y1_q3_value']);
            unset($request['y1_q4_value']);
            unset($request['y2_q1_value']);
            unset($request['y2_q2_value']);
            unset($request['y2_q3_value']);
            unset($request['y2_q4_value']);
            unset($request['y3_q1_value']);
            unset($request['y3_q2_value']);
            unset($request['y3_q3_value']);
            unset($request['y3_q4_value']);
            unset($request['mtp_value']);
            unset($request['q1_pct']);
            unset($request['q2_pct']);
            unset($request['q3_pct']);
            unset($request['q4_pct']);
            if ($table != 'kpi_def') {
                unset($request['target_determining_method']);
            }
            unset($request['value_periodicity']);
            unset($request['target_start_date']);

        }

        if ($table == 'benchmark_kpi_rel' || $table == 'kpi_target') {
            unset($request['tenant_id']);
        }

        if ($table == 'benchmark_kpi_rel' && !empty($request->benchmarket_id)) {

            $data = \DB::select(\DB::raw("select * from $table where kpi_id=$request->kpi_id"));

            if (count($data) > 0) {
                if (!empty($request->kpi_id)) {
                    $request->id = $request->kpi_id;
                    //unset($request['id']);
                }
            } /*else {
                echo 'dddd0';
            }*/

        }

        if ($table == 'kpi_target') {
            $base_y_value = (int)(str_replace(",", '', $request['base_y_value']));
            $y1_value = (int)(str_replace(",", '', $request['y1_value']));
            $y2_value = (int)(str_replace(",", '', $request['y2_value']));
            $y3_value = (int)(str_replace(",", '', $request['y3_value']));
            $mtp_value = (int)(str_replace(",", '', $request['mtp_value']));

            $y1_q1_value = (int)(str_replace(",", '', $request['y1_q1_value']));
            $y1_q2_value = (int)(str_replace(",", '', $request['y1_q2_value']));
            $y1_q3_value = (int)(str_replace(",", '', $request['y1_q3_value']));
            $y1_q4_value = (int)(str_replace(",", '', $request['y1_q4_value']));

            $y2_q1_value = (int)(str_replace(",", '', $request['y2_q1_value']));
            $y2_q2_value = (int)(str_replace(",", '', $request['y2_q2_value']));
            $y2_q3_value = (int)(str_replace(",", '', $request['y2_q3_value']));
            $y2_q4_value = (int)(str_replace(",", '', $request['y2_q4_value']));

            $y3_q1_value = (int)(str_replace(",", '', $request['y3_q1_value']));
            $y3_q2_value = (int)(str_replace(",", '', $request['y3_q2_value']));
            $y3_q3_value = (int)(str_replace(",", '', $request['y3_q3_value']));
            $y3_q4_value = (int)(str_replace(",", '', $request['y3_q4_value']));

            $max_value = (int)(str_replace(",", '', $request['max_value']));
            $min_value = (int)(str_replace(",", '', $request['min_value']));

            if ($valType == 2) {

                $request['base_y_value'] = $base_y_value / 100;
                $request['y1_value'] = $y1_value / 100;
                $request['y2_value'] = $y2_value / 100;
                $request['y3_value'] = $y3_value / 100;
                $request['mtp_value'] = $mtp_value / 100;

                $request['y1_q1_value'] = $y1_q1_value / 100;
                $request['y1_q2_value'] = $y1_q2_value / 100;
                $request['y1_q3_value'] = $y1_q3_value / 100;
                $request['y1_q4_value'] = $y1_q4_value / 100;

                $request['y2_q1_value'] = $y2_q1_value / 100;
                $request['y2_q2_value'] = $y2_q2_value / 100;
                $request['y2_q3_value'] = $y2_q3_value / 100;
                $request['y2_q4_value'] = $y2_q4_value / 100;

                $request['y3_q1_value'] = $y3_q1_value / 100;
                $request['y3_q2_value'] = $y3_q2_value / 100;
                $request['y3_q3_value'] = $y3_q3_value / 100;
                $request['y3_q4_value'] = $y3_q4_value / 100;
                $request['max_value'] = $max_value / 100;
                $request['min_value'] = $min_value / 100;
            } else {
                $request['base_y_value'] = $base_y_value;
                $request['y1_value'] = $y1_value;
                $request['y2_value'] = $y2_value;
                $request['y3_value'] = $y3_value;
                $request['mtp_value'] = $mtp_value;

                $request['y1_q1_value'] = $y1_q1_value;
                $request['y1_q2_value'] = $y1_q2_value;
                $request['y1_q3_value'] = $y1_q3_value;
                $request['y1_q4_value'] = $y1_q4_value;

                $request['y2_q1_value'] = $y2_q1_value;
                $request['y2_q2_value'] = $y2_q2_value;
                $request['y2_q3_value'] = $y2_q3_value;
                $request['y2_q4_value'] = $y2_q4_value;

                $request['y3_q1_value'] = $y3_q1_value;
                $request['y3_q2_value'] = $y3_q2_value;
                $request['y3_q3_value'] = $y3_q3_value;
                $request['y3_q4_value'] = $y3_q4_value;
                $request['max_value'] = $max_value;
                $request['min_value'] = $min_value;
            }

            /* Start Always Percentage */
            $request['annual_rate_h'] = $request['annual_rate_h'] / 100;
            $request['margin_pct'] = $request['margin_pct'] / 100;

            $request['y1_rate_p'] = $request['y1_rate_p'] / 100;
            $request['y2_rate_p'] = $request['y2_rate_p'] / 100;
            $request['y3_rate_p'] = $request['y3_rate_p'] / 100;
            $request['q1_pct'] = $request['q1_pct'] / 100;
            $request['q2_pct'] = $request['q2_pct'] / 100;
            $request['q3_pct'] = $request['q3_pct'] / 100;
            $request['q4_pct'] = $request['q4_pct'] / 100;
            /* End */
        }

        $model = new Dynamic($request->all());
        $model->setTable($table);
        $kpiidvalue = $request->get('kpi_id');
        if ($table == 'kpi_target') {
            $dataid = \DB::select(\DB::raw("select value_period from kpi_def where id=$kpiidvalue"));
            $valueperiod = $dataid[0]->value_period;

            $fiscalyear = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(),fye.end_date from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1 /*or our tenant specific value (from environment variables)*/ and /*m.subtenant_id = <our parameter value> and*/ fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() <= fye.end_date;"));
            $fiscalyearstartdate = $fiscalyear[0]->start_date;
            $fiscalyearenddate = $fiscalyear[0]->end_date;
            $mtp_id = $fiscalyear[0]->id;
            date_default_timezone_set('UTC');

            $date = $fiscalyearstartdate;
            $end_date = $fiscalyearenddate;
            $count = 0;
            $targetmonth = 0;
            $targetyear = 1;
        }

        $isHistory = false;
        if (empty($request->id) || !isset($request->id) || $request->id === 0) {
            if ($model->save($request->all())) {
                if ($table == 'kpi_def') {
                    if ($request->get('target_determining_method') == 1) {
                        $isHistory = true;
                    }
                }
                if ($table == 'kpi_target') {
                    $kpiid = $model->id;
                    $valueperiodicity = $request->value_periodicity;
                    $quarters = $this->get_quarters($fiscalyearstartdate, $end_date);
                    $biannuals = $this->get_biannuals($fiscalyearstartdate, $end_date);
                    $annuals = $this->get_yearly($fiscalyearstartdate, $end_date);
                    if ($valueperiodicity == 1) {

                        while (strtotime($date) <= strtotime($end_date)) {
                            $last_day = date('Y-m-t', strtotime($date));
                            $count++;
                            $targetmonth++;
                            $targetyear++;
                            if ($targetmonth > 12)

                                $targetmonth = 1;
                            else {
                                $targetyear--;
                            }

                            DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpiid,$targetyear,$targetmonth,'$last_day')");

                            $date = date("d-m-Y", strtotime("+31 day", strtotime($date)));
                        }
                    }

                    if ($valueperiodicity == 3) {
                        $count = 1;
                        $targetmonth = 0;
                        $targetyear = 1;
                        foreach ($quarters as $quarter) {
                            $targetmonth = $targetmonth + 3;

                            if ($count > 4) {
                                $targetmonth = 3;
                                $targetyear = $targetyear + 1;
                                $count = 1;

                            }

                            $count++;
                            DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpiid,$targetyear,$targetmonth,'$quarter')");
                        }

                    }

                    if ($valueperiodicity == 6) {
                        $count = 1;
                        $targetmonth = 0;
                        $targetyear = 1;
                        foreach ($biannuals as $biannual) {
                            $targetmonth = $targetmonth + 6;
                            if ($count > 2) {
                                $targetmonth = 6;
                                $targetyear = $targetyear + 1;
                                $count = 1;
                            }

                            $count++;
                            DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpiid,$targetyear,$targetmonth,'$biannual')");

                        }
                    }
                    if ($valueperiodicity == 12) {
                        $count = 1;
                        $targetmonth = 0;
                        $targetyear = 1;
                        foreach ($annuals as $annual) {
                            $targetmonth = $targetmonth + 12;
                            if ($count > 1) {
                                $targetmonth = 12;
                                $targetyear = $targetyear + 1;
                                $count = 1;

                            }
                            $count++;
                            DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpiid,$targetyear,$targetmonth,'$annual')");

                        }
                    }
                    /*                    $i = 1;
                                        $j = 3;
                                        $k = 1;
                                        foreach ($quarters as $quarter) {
                                            $frommonth = $i;
                                            $tomonth = $j;
                                            $fromyear = $k;
                                            $toyear = $k;

                                            if ($i = 10) {
                                                $i = 1;
                                            }

                                            if ($j = 12) {
                                                $j = 3;
                                            }
                                            if ($frommonth > 10) {
                                                $frommonth = 1;
                                                //$k=$toyear+1;
                                                $fromyear = $toyear = $k + 1;
                                            }
                                            if ($tomonth > 12) {
                                                $tomonth = 3;
                                            }

                                            DB::insert("INSERT INTO `kpi_values_av` (`kpi_target_id`, `month_count`,  `target_date`,`from_month`,`to_month`,`from_year`,`to_year`) values ($kpiid,3,'$quarter',$frommonth,$tomonth,$fromyear,$toyear)");
                                            $j = $tomonth + 3;
                                            $i = $frommonth + 3;
                                            $k = $fromyear;

                                        }


                                        $ii = 1;
                                        $jj = 6;
                                        $kk = 1;
                                        foreach ($biannuals as $biannual) {

                                            $frommonth = $ii;
                                            $tomonth = $jj;
                                            $fromyear = $kk;
                                            $toyear = $kk;

                                            if ($ii = 7) {
                                                $ii = 1;
                                            }

                                            if ($jj = 12) {
                                                $jj = 6;
                                            }
                                            if ($frommonth > 7) {
                                                $frommonth = 1;
                                                //$k=$toyear+1;
                                                $fromyear = $toyear = $kk + 1;
                                            }
                                            if ($tomonth > 12) {
                                                $tomonth = 6;
                                            }

                    //                        DB::insert("INSERT INTO `kpi_values_av` (`kpi_target_id`, `month_count`,  `target_date`,`from_month`,`to_month`,`from_year`,`to_year` ) values ($kpiid,6,'$biannual',$frommonth,$tomonth,$fromyear,$toyear)");
                                            $jj = $tomonth + 6;
                                            $ii = $frommonth + 6;
                                            $kk = $fromyear;

                                        }
                                        $frommonth = 1;
                                        $tomonth = 12;
                                        $fromyear = 1;
                                        $toyear = 1;
                                        foreach ($annuals as $annual) {

                    //                        DB::insert("INSERT INTO `kpi_values_av` (`kpi_target_id`, `month_count`,  `target_date`,`from_month`,`to_month`,`from_year`,`to_year`) values ($kpiid,12,'$annual',$frommonth,$tomonth,$fromyear,$toyear)");
                                            $fromyear = $fromyear + 1;
                                            $toyear = $toyear + 1;

                                        }
                                        DB::insert("INSERT INTO `kpi_values_av` (`kpi_target_id`, `month_count`,  `target_date`,`from_month`,`to_month`,`from_year`,`to_year` ) values ($kpiid,36,'$end_date',1,12,1,3)");*/

                }

                $redis = Redis::connection();
                $redis->publish('message', $model);
                /*$redis1 = Redis::connection();
                $redis1->publish('dashboard', '');*/

                return response()->json([
                    "code" => 200,
                    "data" => $model->id,
                    "isHistory" => $isHistory,
                    "msg" => " data inserted successfully"
                ]);
            }
        } else {
            $update = DB::table($table);
            /////// For Audit Purpose
            $model = new Dynamic($request->all());
            $model->setTable($table);
            $query = $model->find($request->id);
            $updates = $request->all();
            $query->update($updates);
            /////// For Audit Purpose

            if ($table == 'benchmark_kpi_rel') {
                $update->where('kpi_id', $request->id);
            } else {
                $update->where('id', $request->id);

                if ($table == 'kpi_target') {
                    ///start To update the values in kpi values if value periodicity changes
                    $data = \DB::select(\DB::raw("SELECT count(*) as count FROM `kpi_target` WHERE kpi_id=$request->kpi_id and mtp_id=$request->mtp_id and  value_periodicity=$request->value_periodicity"));

                    if ($data[0]->count === 0) {
                        \DB::select(\DB::raw("delete from kpi_values  where kpi_target_id=$request->id"));

                        $valueperiodicity = $request->value_periodicity;
                        $quarters = $this->get_quarters($fiscalyearstartdate, $end_date);
                        $biannuals = $this->get_biannuals($fiscalyearstartdate, $end_date);
                        $annuals = $this->get_yearly($fiscalyearstartdate, $end_date);
                        if ($valueperiodicity == 1) {

                            while (strtotime($date) <= strtotime($end_date)) {

                                $last_day = date('Y-m-t', strtotime($date));
                                $count++;
                                $targetmonth++;
                                $targetyear++;
                                if ($targetmonth > 12)

                                    $targetmonth = 1;
                                else {
                                    $targetyear--;
                                }

                                DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($request->id,$targetyear,$targetmonth,'$last_day')");

                                $date = date("d-m-Y", strtotime("+31 day", strtotime($date)));
                            }
                        }
                        if ($valueperiodicity == 3) {
                            $count = 1;
                            $targetmonth = 0;
                            $targetyear = 1;
                            foreach ($quarters as $quarter) {
                                $targetmonth = $targetmonth + 3;

                                if ($count > 4) {
                                    $targetmonth = 3;
                                    $targetyear = $targetyear + 1;
                                    $count = 1;

                                }

                                $count++;
                                DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($request->id,$targetyear,$targetmonth,'$quarter')");
                            }

                        }

                        if ($valueperiodicity == 6) {
                            $count = 1;
                            $targetmonth = 0;
                            $targetyear = 1;
                            foreach ($biannuals as $biannual) {
                                $targetmonth = $targetmonth + 6;
                                if ($count > 2) {
                                    $targetmonth = 6;
                                    $targetyear = $targetyear + 1;
                                    $count = 1;
                                }

                                $count++;
                                DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($request->id,$targetyear,$targetmonth,'$biannual')");

                            }
                        }
                        if ($valueperiodicity == 12) {
                            $count = 1;
                            $targetmonth = 0;
                            $targetyear = 1;
                            foreach ($annuals as $annual) {
                                $targetmonth = $targetmonth + 12;
                                if ($count > 1) {
                                    $targetmonth = 12;
                                    $targetyear = $targetyear + 1;
                                    $count = 1;
                                }
                                $count++;
                                DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($request->id,$targetyear,$targetmonth,'$annual')");

                            }
                        }
                    }
                }
                ///end To update the values in kpi values if value periodicity changes
            }
            if ($table == 'kpi_def') {
                $valueperiodolddata = \DB::select(\DB::raw("select value_period from kpi_def where id=$request->id"));

                $valueperiodold = $valueperiodolddata[0]->value_period;
                $methoddata = \DB::select(\DB::raw("select target_determining_method from kpi_def where id=$request->id"));

                $methodold = $methoddata[0]->target_determining_method;
                //echo $valueperiodold . "==" . $request->value_period;


                if ($valueperiodold != $request->value_period || $methodold != $request->target_determining_method) {

                    $this->kpivaluesdelete($request->id, $request->value_period, $request->target_determining_method);

                }
//                if ($valueperiodold == $request->value_period && $methodold != $request->target_determining_method) {
//
//
//                    $this->kpivaluesdeletemethodchange($request->id, $request->value_period, $request->target_determining_method);
//
//                }

            }
            $update->update(($request->all()));
            $isHistory = false;
            if ($table == 'kpi_def') {
                if ($request->get('target_determining_method') == 0) {
                    $isHistory = true;
                }
            }
            if ($update) {

                 $redis = Redis::connection();
                $redis->publish('message', $model);

                /* $redis1 = Redis::connection();
                 $redis1->publish('dashboard', '');*/

                return response()->json([
                    "code" => 200,
                    "data" => $model->id,
                    "isHistory" => true,
                    "msg" => "data updated successfully"
                ]);
            }
        }
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function get_quarters($start_date, $end_date)
    {

        $quarters = array();
        $start_month = date('m', strtotime($start_date));
        $start_year = date('Y', strtotime($start_date));
        $end_month = date('m', strtotime($end_date));
        $end_year = date('Y', strtotime($end_date));

        $start_quarter = ceil($start_month / 3);

        $end_quarter = ceil($end_month / 3);
        $quarter = $start_quarter; // variable to track current quarter
        for ($y = $start_year; $y <= $end_year; $y++) {
            if ($y == $end_year)
                $max_qtr = $end_quarter;
            else
                $max_qtr = 4;

            for ($q = $quarter; $q <= $max_qtr; $q++) {
                $current_quarter = new  \stdClass();
                $end_month_num = $this->zero_pad($q * 3);
                $current_quarter = "$y-$end_month_num-" . $this->month_end_date($y, $end_month_num);
                $quarters[] = $current_quarter;
                unset($current_quarter);
            }
            $quarter = 1; // reset to 1 for next year
        }
        return $quarters;
    }

    /**
     * @param $number
     * @return string
     */
    public function zero_pad($number)
    {
        if ($number < 10)
            return "0$number";

        return "$number";
    }

    /**
     * @param $year
     * @param $month_number
     * @return false|string
     */
    function month_end_date($year, $month_number)
    {
        date_default_timezone_set('UTC');

        return date("t", strtotime("$year-$month_number-01"));
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function get_biannuals($start_date, $end_date)
    {

        $start = new \DateTime($start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($end_date);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('6 month');
        $period = new \DatePeriod($start, $interval, $end);
        foreach ($period as $key => $dt) {

            if ($key != 0) {
                $stop_date = date('Y-m-d', strtotime($dt->format("Y-m-d") . ' -1 day'));
                // echo $stop_date;
                $biannuals[] = $stop_date;
            }
        }
        $end_date = strtotime($end_date);
        $end_date = date('Y-m-d', $end_date);
        $biannuals[] = $end_date;
        return $biannuals;
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function get_yearly($start_date, $end_date)
    {

        $start = new \DateTime($start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($end_date);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('12 month');
        $period = new \DatePeriod($start, $interval, $end);
        foreach ($period as $key => $dt) {

            if ($key != 0) {
                $stop_date = date('Y-m-d', strtotime($dt->format("Y-m-d") . ' -1 day'));
                // echo $stop_date;
                $annuals[] = $stop_date;
            }
        }
        $end_date = strtotime($end_date);

        $end_date = date('Y-m-d', $end_date);

        $annuals[] = $end_date;
        return $annuals;
    }

    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('clientapp::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($formId, $id)
    {
        $data = \DB::select(\DB::raw("select db_table from fg_form where id=$formId"));
        $dbname = $data[0]->db_table;
        $Tabledataresult = \DB::select(\DB::raw("delete from $dbname  where id=$id"));

        if ($Tabledataresult) {
            return response()->json([
                "code" => 200,
                "msg" => "Data Deleted"
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * Fetch Citiys
     */
    public function fetchCitiesForCountry($id)
    {
        $country = \DB::select(\DB::raw("select id, name from " . env('CITY_TABLE') . " where country_id='" . $id . "'"));
        return response()->json([
            "code" => 200,
            "cities" => $country
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * Fetch Secotr Child
     */
    public function fetchSectorChild(Request $request, $id)
    {
        /*$tenants = SubTenant::with('tree')->orWhere('parent_id', 0)->orWhere('parent_id', $id)->where('tenant_id', \Auth::user()->tenant_id)->get();

        $tenants = $this->_replace_amp($tenants);*/

        $sqlData = explode("==", $request->get('sql'));

        $sql = ($sqlData[1]);
        $tenant = ($request->get('subtenant_id'));
        //$subtenant = ($request->get('model')['child_subtenant_id']);
        //die;

        $token = array(
            //'scope_table' => "'" . $id . "'",
            'subtenant_id' => $id,
            'tenant_id' => env('TENANT_ID'),
            // 'USER_EMAIL'=> $userEmail
        );
        $pattern = '[%s]';
        foreach ($token as $key => $val) {
            $varMap[sprintf($pattern, $key)] = $val;
        }

        $sqlContent = strtr($sql, $varMap);

        $tenants = \DB::select(\DB::raw($sqlContent));

        if ($tenants) {
            return response()->json([
                "code" => 200,
                "subTenants" => $tenants
            ]);
        }
    }

    /**
     * @param $id
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     * Fetch User Data
     */
    public function fetchUserData($id, $type)
    {
        /*$tenants = User::where('tenant_id',\Auth::user()->tenant_id)->where('id', $id)->get();*/
        $userData = \DB::select(\DB::raw("select users.id, users.name, email,phone_internal,subtenant.name as subtenantname from users inner JOIN subtenant on users.subtenant_id = subtenant.id where users.id='" . $id . "'"));

        $dataUser = [];
        foreach ($userData as $key => $val) {
            /* $varData1['id'] = 0;
             $varData1['name'] = $val->name;
             $varData2['id'] = 0;
             $varData2['name'] = $val->email;
             $varData3['id'] = 0;
             $varData3['name'] = $val->subtenantname;
             $varData4['id'] = 0;
             $varData4['name'] = $val->phone_internal;*/
            $dataUser[$type . '_name'] = $val->name;
            $dataUser[$type . '_email'] = $val->email;
            $dataUser[$type . '_subtenant_id'] = $val->subtenantname;
            $dataUser[$type . '_phone_internal'] = $val->phone_internal;
        }
        /*$dataUser[$type . '_name'][] = $varData1;
        $dataUser[$type . '_email'][] = $varData2;
        $dataUser[$type . '_subtenant_id'][] = $varData3;
        $dataUser[$type . '_phone_internal'][] = $varData4;*/

        // var_dump($dataUser);
        if ($userData) {
            return response()->json([
                "code" => 200,
                "userData" => $dataUser
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * Fetch banchmark Data
     */
    public function fetchBenchMarkData($id)
    {
        //echo $type;
        /*$tenants = User::where('tenant_id',\Auth::user()->tenant_id)->where('id', $id)->get();*/
        $userData = \DB::select(\DB::raw("select def.region_of_origin as region_origin, norm.norm_value, norm.norm_date from benchmark_def def INNER JOIN benchmark_norm norm on def.id = norm.benchmark_id where def.id='" . $id . "'"));

        $dataUser = $varData1 = $varData2 = $varData3 = [];
        if ($userData) {
            foreach ($userData as $key => $val) {
                $dataUser['region_origin'] = $val->region_origin;
                $dataUser['norm_value'] = $val->norm_value;
                $dataUser['norm_date'] = $val->norm_date;
            }
        }
        if ($userData) {
            return response()->json([
                "code" => 200,
                "userData" => $dataUser
            ]);
        } else {
            $dataUser['region_of_origin'] = '';
            $dataUser['norm_value'] = '';
            $dataUser['norm_date'] = '';
            return response()->json([
                "code" => 200,
                "userData" => $dataUser
            ]);
        }
    }

    /**
     * @param $array
     * @param int $level
     * @return array|bool
     */
    public function _replace_amp($array, $level = 0)
    {
        if ($array->isEmpty()) {
            return FALSE;
        }
        $dash = '--';
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = array(
                'id' => $value->id,
                'name' => str_repeat($dash, $level) . (($level == 0) ? '' : '>') . $value->name
            );
            if (!$value->tree->isEmpty()) {
                $result = array_merge($result, $this->_replace_amp($value->tree, $level + 1));
            }
        }

        return $result;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProcessObject(Request $request, $id)
    {

        $sqlData = explode("==", $request->get('sql'));

        $sql = ($sqlData[1]);
        //$tenant = ($request->get('subtenant_id'));
        $subtenant = ($request->get('model')['child_subtenant_id']);
        //die;

        //$sql = "select p.id, p.name from process_def p, process_subtenant_rel r1 where [arg_scope_table] = 'process_def' and r1.process_id = p.id and r1.subtenant_id = [arg_sub_tenant_id] union select o.id, o.name from objective o, org_objectives r2 where [arg_scope_table] = 'objective' and r2.objective_id = o.id and r2.tenant_id = [arg_tenant_id] and r2.subtenant_id = [arg_sub_tenant_id] or [arg_sub_tenant_id] = null";

        $token = array(
            'scope_table' => "'" . $id . "'",
            'subtenant_id' => $subtenant,
            'tenant_id' => env('TENANT_ID'),
            // 'USER_EMAIL'=> $userEmail
        );
        $pattern = '[%s]';
        foreach ($token as $key => $val) {
            $varMap[sprintf($pattern, $key)] = $val;
        }

        $sqlContent = strtr($sql, $varMap);
        //die;
        //$sql = "select p.id, p.name from process_def p, subtenant_process_rel r1 where ".str_replace("{{arg_scope_table}}", $id)." = 'process_def' and r1.process_id = p.id and r1.subtenant_id ".str_replace("{{arg_sub_tenant_id}}", $subtenant)." union select o.id, o.name from objective o, org_objectives r2 where ".str_replace("{{arg_scope_table}}", $id)." = 'objective' and r2.objective_id = o.id and r2.tenant_id =".str_replace("{{arg_tenant_id}}", $tenant)." and    (r2.subtenant_id = ".str_replace("{{arg_sub_tenant_id}}", $subtenant)." or ".str_replace("{{arg_sub_tenant_id}}", $subtenant)." = null)";

        $country = \DB::select(\DB::raw($sqlContent));
        return response()->json([
            "code" => 200,
            "process_obj" => $country
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * Kpi List
     */
    public function kpidefList()
    {
        $sql = '';
        /*if($this->user->is('Strategy Executive')) {
            $sql = ' INNER JOIN users user ON (user.id = def.user_of_data or user.id=def.user_of_coordination)';
        }

        if($this->user->is('Department Communicator')) {
            $sql = ' INNER JOIN users user ON (user.id = def.user_of_auditing or user.id=def.user_of_contact)';
        }

        if($this->user->is('Admin')) {
            $sql = '';
        }*/
        $PriorityType = \DB::select(\DB::raw("select @rownum:=@rownum+1 no, def.symbol,def.target_determining_method, def.id, def.name, def.description, def.active_status, def.status,def.reject_reason,sub.name as subtenant_name, def.subtenant_id, def.child_subtenant_id, def.kpi_cat , ( select min(v.target_date) from kpi_values v, kpi_target t, mtp, fiscal_year fys, fiscal_year fye where v.kpi_target_id = t.id and t.kpi_id = def.id and v.actual_value is null and t.mtp_id = mtp.id and mtp.mtp_start = fys.id and mtp.mtp_end = fye.id and CURDATE() >= fys.start_date and CURDATE() <= fye.end_date ) next_reading_date, (select count(1) from kpi_exception ke where ke.kpi_id = def.id and ke.resolve_status = 0) as exception_count FROM (SELECT @rownum:=0) r, kpi_def def INNER JOIN subtenant sub on sub.id = def.child_subtenant_id $sql order by CAST(def.symbol AS UNSIGNED), def.symbol ASC"));
        if ($PriorityType) {
            return response()->json([
                "code" => 200,
                "PRCTypes" => $PriorityType
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => "data not found"
        ]);
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return \Illuminate\Http\JsonResponse
     * Kpi History List
     */
    public function kpidefListHistory($start_date, $end_date)
    {
        $sql = '';
        if ($start_date != 'null') {
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));
            $sql = "and target.target_start_date=CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE)";
        }

        $Kpidefhistories = \DB::select(\DB::raw("select def.id as defid, def.name, def.description, def.active_status, def.status,def.reject_reason,sub.name as subtenant_name, def.subtenant_id, def.child_subtenant_id, def.kpi_cat ,(
	  select min(v.target_date) from kpi_values v, kpi_target t, mtp, fiscal_year fys,  fiscal_year fye where
		v.kpi_target_id = t.id and
		t.kpi_id = def.id and
		v.actual_value is null and
		t.mtp_id = mtp.id and
		mtp.mtp_start = fys.id and
		mtp.mtp_end = fye.id and
		CURDATE() >= fys.start_date and
		CURDATE() <= fye.end_date
	) next_reading_date FROM kpi_def def INNER JOIN subtenant sub  on sub.id = def.child_subtenant_id   $sql order by def.id desc"));

        foreach ($Kpidefhistories as $key => $Kpidefhistory) {
            if (isset($start_date)) {
                $Kpidefhistory->start_date = $start_date;
                $Kpidefhistory->end_date = $end_date;
            }
            $Kpidefhistory1[] = $Kpidefhistory;
        }

        if (isset($Kpidefhistory1)) {
            return response()->json([
                "code" => 200,
                "KPIHistory" => $Kpidefhistories
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => "data not found"
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * Load Tenant
     */
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

    /**
     * @return \Illuminate\Http\JsonResponse
     * Load Category
     */
    public function loadCategory()
    {
        $kpiCat = \DB::select(\DB::raw("select id, name from kpi_cat"));
        if ($kpiCat) {
            return response()->json([
                "code" => 200,
                "kpiCat" => $kpiCat
            ]);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * Load MTP
     */
    public function loadMtp()
    {
        $kpiMtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(),fye.end_date from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1  and  fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() >= fye.end_date order by id DESC limit 1"));

        foreach ($kpiMtp as $key => $kpiMtps) {
            if (isset($kpiMtps->start_date)) {
                $kpiMtps->start_date = date("d M Y", strtotime($kpiMtps->start_date));
                $kpiMtps->end_date = date("d M Y", strtotime($kpiMtps->end_date));
            }
            $kpiMtp1[] = $kpiMtps;
        }
        if ($kpiMtp1) {
            return response()->json([
                "code" => 200,
                "kpiMtp" => $kpiMtp1
            ]);
        }
    }

    /**
     * @param $mtpstart_date
     * @param $mtpend_date
     * @return \Illuminate\Http\JsonResponse
     * Loaf Fiscal year
     */
    public function loadfiscal($mtpstart_date, $mtpend_date)
    {
        $sql = '';
        if ($mtpstart_date != 'null') {
            $mtpstart_date = date('Y-m-d', strtotime($mtpstart_date));
            $mtpend_date = date('Y-m-d', strtotime($mtpend_date));
            $sql = "where start_date>='$mtpstart_date' and end_date <='$mtpend_date'";
        }
        $kpiFiscal = \DB::select(\DB::raw("select start_date,end_date from fiscal_year $sql"));

        foreach ($kpiFiscal as $key => $kpiFiscals) {
            if (isset($kpiFiscals->start_date)) {
                $kpiFiscals->mtpstart_date = date("d M Y", strtotime($kpiFiscals->start_date));
                $kpiFiscals->mtpend_date = date("d M Y", strtotime($kpiFiscals->end_date));
            }
            $kpiFiscal1[] = $kpiFiscals;
        }
        if ($kpiFiscal1) {
            return response()->json([
                "code" => 200,
                "kpifiscal" => $kpiFiscal1
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * Load Sub Tenant
     */
    public function loadSubTenants($id)
    {
        //$subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), cast(id as char(200)) from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '-'), '>', s.name), s.parent_id, CONCAT(c.level, '-'), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id) select id, name from cte order by path"));
        $subtenants = \DB::select(\DB::raw("WITH RECURSIVE cte (id, name, parent_id, level, path) AS (select id, name, parent_id, CAST('' AS CHAR(10)), concat( cast(id as char(200)), '_') from subtenant where parent_id = $id UNION ALL select s.id, concat(CONCAT(c.level, '='), '> ', s.name), s.parent_id, CONCAT(c.level, '='), CONCAT(c.path, ',', s.id) from subtenant s inner join cte c on s.parent_id = c.id UNION ALL select null, repeat('_', 50), $id, '', CONCAT(id, '') from subtenant where parent_id = $id) select id, name from cte order by path"));
        if ($subtenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $subtenants
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Kpi Approve and Reject
     */
    public function kpiApproveReject(Request $request)
    {

        /////// For Audit Purpose
        $model = new Dynamic($request->all());
        $model->setTable("kpi_def");
        $id = $request->get('kpi');
        $query = $model->find($id);
        $updates["status"] = $request->get('status');
        $updates["reject_reason"] = $request->get('rejectreason');
        if ($query->update($updates)) {
            $status = $request->get('status');
            $redis = Redis::connection();
            $redis->publish('message', '');

           /* $redis1 = Redis::connection();
            $redis1->publish('dashboard', '');*/

            return response()->json([
                "code" => 200,
                "msg" => ($status == 1) ? 'kpi approved successfully' : 'kpi rejected successfully.please check the rejected reason and Resubmit the form'
            ]);
        }
        return response()->json([
            "code" => 400,
            "msg" => 'failed to update status'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * to store historical data in target table and values table
     */
    public function storehistory(Request $request)
    {
        $kpi_id = $request->get('data');
        $fiscalyear = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(),fye.end_date from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1  and  fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() >= fye.end_date order by id DESC limit 1"));
        $fiscalyearstartdate = $fiscalyear[0]->start_date;
        $fiscalyearenddate = $fiscalyear[0]->end_date;

        $mtp_id = $fiscalyear[0]->id;

        $counttarget = \DB::select(\DB::raw("SELECT id FROM kpi_target  WHERE  mtp_id=$mtp_id and kpi_id=$kpi_id"));
        $counttargetdata = count($counttarget);
        $kpidefvalueperiod = \DB::select(\DB::raw("SELECT target_determining_method,value_period FROM kpi_def  WHERE id=$kpi_id"));
        $value_period = $kpidefvalueperiod[0]->value_period;
        $value_periodicity = 3;
        if ($value_period > 3) {
            $value_periodicity = 12;
        }
        $target_determining_method = $kpidefvalueperiod[0]->target_determining_method;
        $counttargetdata = count($counttarget);
        if ($target_determining_method == 1) {
            if ($counttargetdata == 0) {
                $insert = DB::table('kpi_target')->insertGetId(['kpi_id' => $kpi_id, 'mtp_id' => $mtp_id, 'value_periodicity' => $value_periodicity]);
                $kpitargetid = $insert;
            } else {

                $kpitargetid = $counttarget[0]->id;
            }

            $counthistory = \DB::select(\DB::raw("SELECT count(*) as datacount FROM kpi_values INNER JOIN kpi_target on kpi_target.id = kpi_values.kpi_target_id INNER JOIN kpi_def ON kpi_def.id=kpi_target.kpi_id WHERE  target_date=CAST('$fiscalyearenddate' AS DATE) AND CAST('$fiscalyearstartdate' AS DATE) and kpi_def.id=$kpi_id"));
            $counthistorydata = $counthistory[0]->datacount;

            date_default_timezone_set('UTC');

            //$date = $fiscalyearstartdate;
            $end_date = $fiscalyearenddate;
            $count = 1;
            $targetmonth = 0;
            $targetyear = 1;

            if ($kpi_id && $counthistorydata == 0) {
                if ($value_period > 3) {
                    $count = 1;
                    $targetmonth = 0;
                    $targetyear = 1;
                    $annuals = $this->get_yearly($fiscalyearstartdate, $end_date);
                    foreach ($annuals as $annual) {
                        $targetmonth = $targetmonth + 12;
                        if ($count > 1) {
                            $targetmonth = 12;
                            $targetyear = $targetyear + 1;
                            $count = 1;
                        }
                        $count++;
                        DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpitargetid,$targetyear,$targetmonth,'$annual')");

                    }
                } else {
                    $i = 1;
                    $j = 3;
                    $k = 1;
                    $quarters = $this->get_quarters($fiscalyearstartdate, $end_date);
                    foreach ($quarters as $quarter) {
                        $targetmonth = $targetmonth + 3;
                        if ($count > 4) {
                            $targetmonth = 3;
                            $targetyear = $targetyear + 1;
                            $count = 1;
                            // $targetyear=$targetyear+1;
                        }
                        $count++;
                        DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpitargetid,$targetyear,$targetmonth,'$quarter')");
                    }
                }
                return response()->json([
                    "code" => 200,
                    "msg" => "data inserted successfully"
                ]);

            } else {
                return response()->json([
                    "code" => 202,
                    "msg" => " histoical data exists"
                ]);
            }
        }
        return response()->json([
            "code" => 200,
            "msg" => "No Data"
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Kpi Active and Deactive
     */
    public function kpiActiveInactive(Request $request)
    {

        /////// For Audit Purpose
        $model = new Dynamic($request->all());
        $model->setTable("kpi_def");
        $id = $request->get('kpi');
        $query = $model->find($id);
        $updates["active_status"] = $request->get('status');
        if ($query->update($updates)) {

            $status = $request->get('status');
            $messageRedis = [];
            $messageRedis['id'] = $query->symbol;
            $messageRedis['message'] = 'updated_by';
            $messageRedis['user'] = auth()->user()->name;
            $messageRedis['userImage'] = auth()->user()->file_name;
            $messageRedis['kpiname'] = $query->name;
            $messageRedis['time'] = date('Y-m-d H:i:s');
            $redis = Redis::connection();
            $redis->publish('message', json_encode($messageRedis, true));

            /*$redis1 = Redis::connection();
            $redis1->publish('dashboard', '');*/

            return response()->json([
                "code" => 200,
                "msg" => ($status == 1) ? 'kpi activated successfully' : 'kpi Deactivated Successfully'
            ]);
        }
        return response()->json([
            "code" => 400,
            "msg" => 'failed to update active and inactive status'
        ]);
    }

    /**
     * @param $value
     * @return array|\Illuminate\Http\JsonResponse
     * load kpi valueslist
     */
    public function kpivalues($value)
    {

        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $currentmtpenddate = $currentmtp[0]->end_date;

//        $kpivalues = \DB::select(\DB::raw(" select k.* ,d.numerator_name as numeratorname,d.denominator_name as denominatorname,d.id as kpiid ,d.name as kpiname, sub.name as subtenant_name,d.value_type
//from kpi_values k, kpi_def d, kpi_target t,subtenant sub
//where d.id = t.kpi_id and k.kpi_target_id = t.id and  d.id=$value and sub.id = d.child_subtenant_id and k.actual_value is null and k.target_date <= CURDATE() and date(k.target_date) BETWEEN CAST('$currentmtpstartdate' AS DATE) AND CAST('$currentmtpenddate' AS DATE)
//"));
        $kpivalues = \DB::select(\DB::raw(" select k.* ,d.numerator_name as numeratorname,d.denominator_name as denominatorname,d.id as kpiid ,d.name as kpiname, sub.name as subtenant_name,d.value_type
from kpi_values k, kpi_def d, kpi_target t,subtenant sub
where d.id = t.kpi_id and k.kpi_target_id = t.id and  d.id=$value and sub.id = d.child_subtenant_id and k.actual_value is null  and date(k.target_date) BETWEEN CAST('$currentmtpstartdate' AS DATE) AND CAST('$currentmtpenddate' AS DATE)
"));

        foreach ($kpivalues as $key => $kpivalue) {
            if (isset($kpivalue->value_type)) {
                if ($kpivalue->value_type == 1) {
                    $kpivalue->value_type = "Number";
                }
                if ($kpivalue->value_type == 2) {
                    $kpivalue->value_type = "Percentage";
                    if ($kpivalue->actual_value) {
                        $kpivalue->actual_value = $kpivalue->actual_value . '%';
                    }
                }
                if ($kpivalue->value_type == 3) {
                    $kpivalue->value_type = "Ratio";
                }
                if ($kpivalue->value_type == 4) {
                    $kpivalue->value_type = "Rate";
                }
            }
            $kpivalues1[] = $kpivalue;
        }

        if ($kpivalues) {
            return [
                "code" => 200,
                'data' => $kpivalues,

            ];
        } else {
            return response()->json([
                "code" => 400,
                "msg" => "No Data"
            ]);
        }

    }

    /**
     * @param $value
     * @return array
     * load kpi value history
     */
    public function kpivalueshistory($value)
    {
        $fiscalyear = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(),fye.end_date from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1  and  fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() >= fye.end_date order by id DESC limit 1"));
        $fiscalyearstartdate = $fiscalyear[0]->start_date;
        $fiscalyearenddate = $fiscalyear[0]->end_date;

        $kpivalues = \DB::select(\DB::raw("select k.* ,d.id as kpiid,t.base_y_value, k.kpi_target_id as targetid ,d.name as kpiname,d.numerator_name as numeratorname,d.denominator_name as denominatorname, sub.name as subtenant_name,d.value_type,d.value_period as value_period from kpi_values k, kpi_def d, kpi_target t,subtenant sub where d.id = t.kpi_id and k.kpi_target_id = t.id and d.id=$value and sub.id = d.child_subtenant_id  and date(k.target_date) BETWEEN CAST('$fiscalyearstartdate' AS DATE) AND CAST('$fiscalyearenddate' AS DATE)"));

        $kpivaluesactualcount = \DB::select(\DB::raw("select COUNT(*) as valuecount from kpi_values k, kpi_def d, kpi_target t,subtenant sub where d.id = t.kpi_id and k.kpi_target_id = t.id and d.id=$value and sub.id = d.child_subtenant_id and k.actual_value is null and date(k.target_date) BETWEEN CAST('$fiscalyearstartdate' AS DATE) AND CAST('$fiscalyearenddate' AS DATE)"));
        $kpivaluesactualcount = $kpivaluesactualcount[0]->valuecount;
        foreach ($kpivalues as $key => $kpivalue) {
            if (isset($kpivalue->value_type)) {
                if ($kpivalue->value_type == 1) {
                    $kpivalue->value_type = "Number";
                }
                if ($kpivalue->value_type == 2) {
                    $kpivalue->value_type = "Percentage";
                    if ($kpivalue->actual_value) {
                        $kpivalue->actual_value = round($kpivalue->actual_value, 2) . '%';
                    }
                }
                if ($kpivalue->value_type == 3) {
                    $kpivalue->value_type = "Ratio";
                }
                if ($kpivalue->value_type == 4) {
                    $kpivalue->value_type = "Rate";
                }
                $kpivalue->actual_value = ($kpivalue->actual_value) ? number_format($kpivalue->actual_value) : '';
            }
            $kpivalues1[] = $kpivalue;
        }

        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $currentmtpenddate = $currentmtp[0]->end_date;
        $iskpivaluescurrentdata = false;
        $kpivaluescurrentactualcount = \DB::select(\DB::raw("select COUNT(*) as valuecount from kpi_values k, kpi_def d, kpi_target t,subtenant sub where d.id = t.kpi_id and k.kpi_target_id = t.id and d.id=$value and sub.id = d.child_subtenant_id and k.actual_value is not null and date(k.target_date) BETWEEN CAST('$currentmtpstartdate' AS DATE) AND CAST('$currentmtpenddate' AS DATE)"));
        $kpivaluescurrentactualcount = $kpivaluescurrentactualcount[0]->valuecount;
        if ($kpivaluescurrentactualcount == 0) {
            $iskpivaluescurrentdata = true;
        }

        if ($kpivalues) {
            return [
                "code" => 200,
                'data' => $kpivalues,
                'kpivaluesactualcount' => $kpivaluesactualcount,
                'kpihistorycount' => (count($kpivalues) > 0 ? count($kpivalues) : 0),
                'kpicurrentvaluecount' => $iskpivaluescurrentdata

            ];
        } else {
            return [
                "code" => 400,
                'kpivaluesactualcount' => 0,
                'kpihistorycount' => (count($kpivalues) > 0 ? count($kpivalues) : 0),

            ];
        }
    }

    /**
     * @param $value
     * @return array|\Illuminate\Http\JsonResponse
     * Kpi Value Edit
     */
    public function kpivaluesedit($value)
    {

        $kpivalues = \DB::select(\DB::raw(" select k.* ,d.id as kpiid ,d.name as kpiname, sub.name as subtenant_name,d.value_type
from kpi_values k, kpi_def d, kpi_target t,subtenant sub
where d.id = t.kpi_id and k.kpi_target_id = t.id and  d.id=$value and sub.id = d.child_subtenant_id and k.target_date <= CURDATE()"));
        foreach ($kpivalues as $key => $kpivalue) {
            if (isset($kpivalue->value_type)) {
                if ($kpivalue->value_type == 1) {
                    $kpivalue->value_type = "Number";
                }
                if ($kpivalue->value_type == 2) {
                    $kpivalue->value_type = "Percentage";
                }
                if ($kpivalue->value_type == 3) {
                    $kpivalue->value_type = "Ratio";
                }
                if ($kpivalue->value_type == 4) {
                    $kpivalue->value_type = "Rate";
                }
            }
            $kpivalues1[] = $kpivalue;
        }

        if ($kpivalues) {
            return [
                'data' => $kpivalues,
            ];
        }

        return response()->json(["code" => 400]);
    }

    /**
     * @param $value
     * @return array|\Illuminate\Http\JsonResponse
     * Kpi Value By Id
     */
    public function kpivaluesbyId($value)
    {

        $kpivalues = \DB::select(\DB::raw(" select kpi_values.* ,kpi_target.base_y_value
from kpi_values inner join kpi_target on kpi_values.kpi_target_id=kpi_target.id  where kpi_values.id='" . $value . "'"));

        if ($kpivalues) {
            return [
                'data' => $kpivalues,
            ];
        }

        return response()->json(["code" => 400]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * KPI Values Update
     */
    public function kpivaluesUpdate(Request $request)
    {
        $year1q1 = $year1q2 = $year1q3 = $year1q4 = false;
        $year2q1 = $year2q2 = $year2q3 = $year2q4 = false;
        $year3q1 = $year3q2 = $year3q3 = $year3q4 = false;
        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $month = date('m', strtotime($currentmtpstartdate));
        $startyear = date('Y', strtotime($currentmtpstartdate));
        $startyear = (int)$startyear;

        $currentmtpenddate = $currentmtp[0]->end_date;
//        if($month==04 || $month==05 || $month==06 ){
//            $firstyear=$year;
//        }
//        echo $month."===";
//        echo $year;
//        die();

        $kpivaluearray = $request->get('kpivalues');
        $moreexists = 0;
        $model = new Dynamic($request->all());
        $model->setTable("kpi_values");
        foreach ($kpivaluearray as $key => $kpivalue) {

            if (isset($kpivalue['actual_value'])) {
                $targetdatesarray[] = $kpivalue['target_date'];
                $targetactvaluearray[$kpivalue['target_date']] = $kpivalue['actual_value'];
                $targetidarray[$kpivalue['target_date']] = $kpivalue['id'];
                $targetnotesarray[$kpivalue['target_date']] = $kpivalue['notes'];
            } else {
                $targetdatenotsarray[] = $kpivalue['target_date'];
            }
        }
        if (isset($targetdatenotsarray)) {

            for ($i = 0; $i < sizeof($targetdatesarray); $i++) {

                if ($targetdatesarray[$i] > min($targetdatenotsarray)) {
                    $allValuesLessThan = false;
                    $moreexists = 1;

                    break;
                } else {
                    $moreexists = 0;
                }

            }
        }
        if ($moreexists == 1) {

            return response()->json([
                "code" => 400,
                "msg" => "there are target date less than this value.so cannot update"
            ]);
        } else {
            foreach ($kpivaluearray as $key => $kpivalue) {
                if (isset($kpivalue['actual_value'])) {
                    $month = date('m', strtotime($kpivalue['target_date']));
                    $year = date('Y', strtotime($kpivalue['target_date']));

                    $month = (int)$month;


                    if ($month == 4 || $month == 5 || $month == 6) {
                        if ($year == $startyear) {
                            $year1q1 = true;
                            //echo "year1q1".$year1q1;
                        }

                        if ($year == $startyear + 1) {
                            $year2q1 = true;
                            //echo "year2q1".$year2q1;
                        }
                        if ($year == $startyear + 2) {
                            $year3q1 = true;
                            // echo "year3q1".$year3q1;
                        }
                    }
                    if ($month == 7 || $month == 8 || $month == 9) {
                        if ($year == $startyear) {
                            $year1q2 = true;
                            // echo "year1q2".$year1q2;
                        }
                        if ($year == $startyear + 1) {
                            $year2q2 = true;
                            //  echo "$year2q2".$year2q2;
                        }
                        if ($year == $startyear + 2) {
                            $year3q2 = true;
                            //   echo "year3q2".$year3q2;
                        }
                    }
                    if ($month == 10 || $month == 11 || $month == 12) {
                        if ($year == $startyear) {
                            $year1q3 = true;
                            // echo "year1q3".$year1q3;

                        }
                        if ($year == $startyear + 1) {
                            $year2q3 = true;
                            // echo "year2q3".$year2q3;
                        }
                        if ($year == $startyear + 2) {
                            $year3q3 = true;
                            //echo "year3q3".$year3q3;
                        }
                    }
                    if ($month == 1 || $month == 2 || $month == 3) {
                        if ($year == $startyear + 1) {
                            $year1q4 = true;
                            //echo "year1q4".$year1q4;
                        }
                        if ($year == $startyear + 2) {
                            $year2q4 = true;
                            // echo "year2q4".$year2q4;
                        }
                        if ($year == $startyear + 3) {
                            $year3q4 = true;
                            // echo "year3q4".$year3q4;
                        }
                    }

                    if (strpos($kpivalue['actual_value'], '%') !== false) {
                        $kpivalue['actual_value'] = str_replace('%', '', $kpivalue['actual_value']);
                    }
                    if (!isset($kpivalue['actual_denominator']) && !(isset($kpivalue['actual_numerator']))) {
                        $kpivalue['actual_denominator'] = null;
                        $kpivalue['actual_numerator'] = null;
                    }
                    $kpivalue['actual_value'] = (float)str_replace(',', '', $kpivalue['actual_value']);
//                    if (\DB::table('kpi_values')
//                        ->where('id', $kpivalue['id'])
//                        ->update(['actual_value' => $kpivalue['actual_value'], 'actual_numerator' => $kpivalue['actual_numerator'], 'actual_denominator' => $kpivalue['actual_denominator'], 'notes' => $kpivalue['notes'], 'actual_date' => Carbon::now()])) {
//
//                    }


                    $id = $kpivalue['id'];
                    $query = $model->find($id);
                    $updates["actual_value"] = $kpivalue['actual_value'];
                    if (!isset($kpivalue['actual_denominator']) && !(isset($kpivalue['actual_numerator']))) {
                        $updates["actual_numerator"] = $kpivalue['actual_numerator'];
                        $updates["actual_denominator"] = $kpivalue['actual_denominator'];
                    }
                    $query->update(['actual_value' => $kpivalue['actual_value'], 'actual_numerator' => $kpivalue['actual_numerator'], 'actual_denominator' => $kpivalue['actual_denominator'], 'notes' => $kpivalue['notes'], 'actual_date' => Carbon::now()]);
                    $kpi_id = $kpivalue['kpiid'];
                    $kpi_targetid = $kpivalue['kpi_target_id'];
                }


            }
/////// For Audit Purpose
//            $modelnew = new Dynamic($request->all());
//            $modelnew->setTable("kpi_values_stats");
//            $id = $kpi_targetid;
//            $query_valuestat = $modelnew->find($id);

            $formuladata = \DB::select(\DB::raw("select kpi_def.value_explanation,kpi_performance_type.formula,kpi_performance_type.factor_1,kpi_performance_type.factor_2 from kpi_def join kpi_performance_type on kpi_def.value_explanation=kpi_performance_type.id where kpi_def.id=37"));


            \DB::select(\DB::raw("set @period_q := 3"));
            \DB::select(\DB::raw("set @period_h:= 6"));
            \DB::select(\DB::raw("set @period_y:= 12"));
            \DB::select(\DB::raw("set @kpi_target_id := 2"));

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
	kvs.kpi_target_id = $kpi_targetid AND
    kvs.year_no > 0
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

                $y_perf = $this->perfcalculate($yvalue, $formula, $ymin_value, $ymax_value, $ybase_value, $factor_1, $factor_2, $ytarget);
               //  echo "yperf=".$y_perf."<br/>";
                $y_prog = $this->progfcalculate($yvalue, $ybase_value, $ytarget);
               // echo "$y_prog=".$y_prog."<br/>";

                if ($year1q1 == true || $year2q1 == true || $year3q1 == true) {

                    $q1_perf = $this->perfcalculate($q1value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q1target);
                    $q1_prog = $this->progfcalculate($q1value, $qbase_value, $q1target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);
                    if($year1q1 == true && $year_no==1) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q1_perf='$q1_perf',q1_prog='$q1_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=1"));
                    }
                    if($year2q1 == true && $year_no==2) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q1_perf='$q1_perf',q1_prog='$q1_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=2"));
                    }
                    if($year3q1 == true && $year_no==3) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q1_perf='$q1_perf',q1_prog='$q1_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=3"));
                    }

                }
                if ($year1q2 == true || $year2q2 == true || $year3q2 == true) {
                    $q2_perf = $this->perfcalculate($q2value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q2target);
                    $q2_prog = $this->progfcalculate($q2value, $qbase_value, $q2target);
                    $h1_perf = $this->perfcalculate($h1value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h1target);
                    $h1_prog = $this->progfcalculate($h1value, $hbase_value, $h1target);
                    if($year1q2 == true && $year_no==1) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q2_perf='$q2_perf',q2_prog='$q2_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=1"));
                    }
                    if($year2q2 == true && $year_no==2) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q2_perf='$q2_perf',q2_prog='$q2_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=2"));
                    }
                    if($year3q2 == true && $year_no==3) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q2_perf='$q2_perf',q2_prog='$q2_prog',h1_perf='$h1_perf',h1_prog='$h1_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=3"));
                    }
                }
                if ($year1q3 == true || $year2q3 == true || $year3q3 == true) {
                    $q3_perf = $this->perfcalculate($q3value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q3target);
                    $q3_prog = $this->progfcalculate($q3value, $qbase_value, $q3target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);
                    if($year1q3 == true && $year_no==1) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q3_perf='$q3_perf',q3_prog='$q3_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=1"));
                    }
                    if($year2q3 == true && $year_no==2) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q3_perf='$q3_perf',q3_prog='$q3_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=2"));
                    }
                    if($year3q3 == true && $year_no==3) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q3_perf='$q3_perf',q3_prog='$q3_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=3"));
                    }

                }
                if ($year1q4 == true || $year2q4 == true || $year3q4 == true) {

                    $q4_perf = $this->perfcalculate($q4value, $formula, $qmin_value, $qmax_value, $qbase_value, $factor_1, $factor_2, $q4target);
                    $q4_prog = $this->progfcalculate($q4value, $qbase_value, $q4target);
                    $h2_perf = $this->perfcalculate($h2value, $formula, $hmin_value, $hmax_value, $hbase_value, $factor_1, $factor_2, $h2target);
                    $h2_prog = $this->progfcalculate($h2value, $hbase_value, $h2target);
                    if($year1q4 == true && $year_no==1) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q4_perf='$q4_perf',q4_prog='$q4_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=1"));
                    }
                    if($year2q4 == true && $year_no==2) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q4_perf='$q4_perf',q4_prog='$q4_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=2"));
                    }
                    if($year3q4 == true && $year_no==3) {
                        $valuestatupdate = \DB::select(\DB::raw("update kpi_values_stats set q4_perf='$q4_perf',q4_prog='$q4_prog',h2_perf='$h2_perf',h2_prog='$h2_prog',y_perf='$y_perf',y_prog='$y_prog' where kpi_target_id=$kpi_targetid and year_no=3"));
                    }
                    ;
                }


//                echo "year1q1" . $year1q1 . "<br>" . "year1q2" . $year1q2 . "<br>" . "year1q3" . $year1q3 . "<br>" . "=" . "year1q4" . $year1q4 . "<br>";
//
//                echo "year2q1" . $year2q1 . "<br>" . "year2q2" . $year2q2 . "<br>" . "year2q3" . $year2q3 . "<br>" . "year2q4" . $year2q4 . "<br>";
//                echo "year3q1" . $year3q1 . "<br>" . "year3q2" . $year3q2 . "<br>" . "year3q3" . $year3q3 . "<br>" . "year3q4" . $year3q4 . "<br>";
            }


            return response()->json([
                "code" => 200,
                "msg" => "data updated successfully"
            ]);
        }
    }

    public function progfcalculate($value, $base_value, $targetvalue)
    {
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
        $base = $base;
        $factor_1 = $factor_1;
        $factor_2 = $factor_2;


        if ($formula) {
            $result = eval("return " . $formula . ";");
        }
        return $result;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * KPI Base value saved
     */
    public function kpibaseyvaluesave(Request $request)
    {

        /////// For Audit Purpose
        $model = new Dynamic($request->all());
        $model->setTable("kpi_target");
        $id = $request->targetid;
        $query = $model->find($id);
        $updates["base_y_value"] = $request->get('base_y_value');
        if ($query->update($updates)) {

            return response()->json([
                "code" => 200,
                "msg" => "data inserted successfully"
            ]);

        }
    }

    /**
     * @param $roleName
     * @return bool
     * Check Roles
     */
    public function is($roleName)
    {
        foreach (auth()->user()->roles()->get() as $role) {
            if ($role->name == $roleName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $value
     * @return array
     * functions added to save on change of value_period
     */
    public function kpivaluetypechangecheck($value)
    {
        $fiscalyear = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(),fye.end_date from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1  and  fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() >= fye.end_date order by id DESC limit 1"));
        $fiscalyearstartdate = $fiscalyear[0]->start_date;
        $fiscalyearenddate = $fiscalyear[0]->end_date;

        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $currentmtpenddate = $currentmtp[0]->end_date;

        $iskpivaluesdata = false;
        $kpivaluesactualcount = \DB::select(\DB::raw("select COUNT(*) as valuecount from kpi_values k, kpi_def d, kpi_target t,subtenant sub where d.id = t.kpi_id and k.kpi_target_id = t.id and d.id=$value and sub.id = d.child_subtenant_id and k.actual_value is not null and date(k.target_date) BETWEEN CAST('$currentmtpstartdate' AS DATE) AND CAST('$currentmtpenddate' AS DATE)"));

        $kpivaluesactualcount = $kpivaluesactualcount[0]->valuecount;
        if ($kpivaluesactualcount == 0) {
            $iskpivaluesdata = true;
        }
        return [
            "code" => 200,
            'data' => $iskpivaluesdata,
        ];
    }

    /**
     * @param $id
     * @param $val
     * @param $method
     * @return array
     * Kpi Value Delete
     */
    public function kpivaluesdelete($id, $val, $method)
    {


        $value_period = $val;

        $fiscalyear = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(),fye.end_date from mtp , fiscal_year fys, fiscal_year fye where mtp.tenant_id = 1  and  fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and CURDATE() >= fys.start_date and CURDATE() >= fye.end_date order by id DESC limit 1"));
        $fiscalyearstartdate = $fiscalyear[0]->start_date;
        $fiscalyearenddate = $fiscalyear[0]->end_date;
        $prevmtp = $fiscalyear[0]->id;

        date_default_timezone_set('UTC');

        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $currentmtpenddate = $currentmtp[0]->end_date;
        $currentmtpid = $currentmtp[0]->id;

        $date = $currentmtpstartdate;
        $end_date = $currentmtpenddate;
        date_default_timezone_set('UTC');

        $historytarget_iddata = \DB::select(\DB::raw("select id from kpi_target where mtp_id=$prevmtp and kpi_id=$id"));
        $currenttarget_iddata = \DB::select(\DB::raw("select id from kpi_target where mtp_id=$currentmtpid and kpi_id=$id"));


        if (count($historytarget_iddata) > 0) {
            $historytarget_id = $historytarget_iddata[0]->id;
            if ($historytarget_id != 0) {


                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $targetdel = \DB::select(\DB::raw(" Delete from kpi_target where id=$historytarget_id"));
                DB::statement('SET FOREIGN_KEY_CHECKS=1');

                $prevmtpvaluesdel = \DB::select(\DB::raw(" Delete from kpi_values where kpi_target_id=$historytarget_id"));
// echo "123456";

                if (count($currenttarget_iddata) > 0) {
                    $currenttarget_id = $currenttarget_iddata[0]->id;
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    $targetdel = \DB::select(\DB::raw(" Delete from kpi_target where id=$currenttarget_id"));
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');

                    $prevmtpvaluesdel = \DB::select(\DB::raw(" Delete from kpi_values where kpi_target_id=$currenttarget_id"));
                }

            }


            /** end history data save  */

        }

        if ($method == 1) {
            $counttarget = \DB::select(\DB::raw("SELECT id FROM kpi_target  WHERE  mtp_id=$prevmtp and kpi_id=$id"));
            $counttargetdata = count($counttarget);
//            $kpidefvalueperiod = \DB::select(\DB::raw("SELECT target_determining_method,value_period FROM kpi_def  WHERE id=$id"));
//            $value_period = $kpidefvalueperiod[0]->value_period;
            $value_periodicity = 3;
            if ($value_period > 3) {
                $value_periodicity = 12;
            }
            $counttargetdata = count($counttarget);

            if ($counttargetdata == 0) {
                $insert = DB::table('kpi_target')->insertGetId(['kpi_id' => $id, 'mtp_id' => $prevmtp, 'value_periodicity' => $value_periodicity]);
                $kpitargetid = $insert;
            } else {

                $kpitargetid = $counttarget[0]->id;
            }

            $counthistory = \DB::select(\DB::raw("SELECT count(*) as datacount FROM kpi_values INNER JOIN kpi_target on kpi_target.id = kpi_values.kpi_target_id INNER JOIN kpi_def ON kpi_def.id=kpi_target.kpi_id WHERE  target_date=CAST('$fiscalyearenddate' AS DATE) AND CAST('$fiscalyearstartdate' AS DATE) and kpi_def.id=$id"));
            $counthistorydata = $counthistory[0]->datacount;

            date_default_timezone_set('UTC');

            //$date = $fiscalyearstartdate;
            $end_date = $fiscalyearenddate;
            $count = 1;
            $targetmonth = 0;
            $targetyear = 1;

            if ($counthistorydata == 0) {
                if ($value_period > 3) {
                    $count = 1;
                    $targetmonth = 0;
                    $targetyear = 1;
                    $annuals = $this->get_yearly($fiscalyearstartdate, $end_date);
                    foreach ($annuals as $annual) {
                        $targetmonth = $targetmonth + 12;
                        if ($count > 1) {
                            $targetmonth = 12;
                            $targetyear = $targetyear + 1;
                            $count = 1;
                        }
                        $count++;
                        DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpitargetid,$targetyear,$targetmonth,'$annual')");

                    }
                } else {
                    $i = 1;
                    $j = 3;
                    $k = 1;
                    $quarters = $this->get_quarters($fiscalyearstartdate, $end_date);
                    foreach ($quarters as $quarter) {
                        $targetmonth = $targetmonth + 3;
                        if ($count > 4) {
                            $targetmonth = 3;
                            $targetyear = $targetyear + 1;
                            $count = 1;
                            // $targetyear=$targetyear+1;
                        }
                        $count++;
                        DB::insert("INSERT INTO `kpi_values` (`kpi_target_id`, `target_year`, `target_month`, `target_date` ) values ($kpitargetid,$targetyear,$targetmonth,'$quarter')");
                    }
                }
                return response()->json([
                    "code" => 200,
                    "msg" => "data inserted successfully"
                ]);

            }
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserOfData($id)
    {

        $userData = \DB::select(\DB::raw("SELECT DISTINCT users.id, CONCAT(users.name, ' ', users.last_name) as name FROM users users INNER JOIN model_has_roles model on model.model_id=users.id INNER JOIN roles role on model.role_id=role.id WHERE (role.name ='Strategy Executive' or users.subtenant_id=$id)"));

        $auditingData = \DB::select(\DB::raw("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name
		from subtenant where
        id = $id -- set your arg here
	UNION ALL
    -- This is the recursive part: It joins to cte
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name
		from subtenant s
        inner join cte c on s.id = c.parent_id
	)
	-- select id, name, subtenant_type, parent_id
	select u.id, CONCAT(u.name, ' ', u.last_name) as name
	from cte, users u where
	u.subtenant_id = cte.id"));

        if ($userData || $auditingData) {
            return response()->json([
                "code" => 200,
                "userData" => $userData,
                "auditingData" => $auditingData,
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserOfAuditing($id)
    {

        $tenants = \DB::select(\DB::raw("select id, name from subtenant s where s.tenant_id=1 and s.subtenant_type_id=3"));
        if ($tenants) {
            return response()->json([
                "code" => 200,
                "tenants" => $tenants
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadKpiDataSector($id)
    {
        if ($id != 'null') {
            $PriorityType = \DB::select(\DB::raw("select @rownum:=@rownum+1 no, def.symbol, def.id, def.name, def.description, def.active_status, def.status,def.reject_reason,sub.name as subtenant_name, def.subtenant_id, def.child_subtenant_id, def.kpi_cat , ( select min(v.target_date) from kpi_values v, kpi_target t, mtp, fiscal_year fys, fiscal_year fye where v.kpi_target_id = t.id and t.kpi_id = def.id and v.actual_value is null and t.mtp_id = mtp.id and mtp.mtp_start = fys.id and mtp.mtp_end = fye.id and CURDATE() >= fys.start_date and CURDATE() <= fye.end_date ) next_reading_date FROM  (SELECT @rownum:=0) r, kpi_def def INNER JOIN subtenant sub on sub.id = def.child_subtenant_id and def.subtenant_id = $id order by CAST(def.symbol AS UNSIGNED) ASC"));
            if ($PriorityType) {
                return response()->json([
                    "code" => 200,
                    "kpidata" => $PriorityType
                ]);
            } else {
                return response()->json([
                    "code" => 200,
                    "kpidata" => []
                ]);
            }
        } else {
            $PriorityType = \DB::select(\DB::raw("select @rownum:=@rownum+1 no, def.symbol, def.id, def.name, def.description, def.active_status, def.status,def.reject_reason,sub.name as subtenant_name, def.subtenant_id, def.child_subtenant_id, def.kpi_cat , ( select min(v.target_date) from kpi_values v, kpi_target t, mtp, fiscal_year fys, fiscal_year fye where v.kpi_target_id = t.id and t.kpi_id = def.id and v.actual_value is null and t.mtp_id = mtp.id and mtp.mtp_start = fys.id and mtp.mtp_end = fye.id and CURDATE() >= fys.start_date and CURDATE() <= fye.end_date ) next_reading_date FROM (SELECT @rownum:=0) r, kpi_def def INNER JOIN subtenant sub on sub.id = def.child_subtenant_id order by CAST(def.symbol AS UNSIGNED) ASC"));
            if ($PriorityType) {
                return response()->json([
                    "code" => 200,
                    "kpidata" => $PriorityType
                ]);
            }
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadKpiDataOrgUnit($id)
    {
        if ($id != 'null') {
            $tenants = \DB::select(\DB::raw("WITH RECURSIVE cte (l1_id, id, parent_id, subtenant_type) AS ( select id, id, parent_id, subtenant_type_id from subtenant where id = $id UNION ALL select c.l1_id, s.id, s.parent_id, s.subtenant_type_id from subtenant s inner join cte c on s.parent_id = c.id) SELECT @rownum:=@rownum+1 no, def.symbol, def.id, def.name, def.description, def.active_status, def.status, def.reject_reason, sub.NAME AS subtenant_name, def.subtenant_id, def.child_subtenant_id, def.kpi_cat, (SELECT min( v.target_date ) FROM kpi_values v, kpi_target t, mtp, fiscal_year fys, fiscal_year fye WHERE v.kpi_target_id = t.id AND t.kpi_id = def.id AND v.actual_value IS NULL AND t.mtp_id = mtp.id AND mtp.mtp_start = fys.id AND mtp.mtp_end = fye.id AND CURDATE() >= fys.start_date AND CURDATE() <= fye.end_date) next_reading_date FROM cte, (SELECT @rownum:=0) r, kpi_def def INNER JOIN subtenant sub ON sub.id = def.child_subtenant_id where def.child_subtenant_id = cte.id ORDER BY no asc, CAST( def.symbol AS UNSIGNED ) desc"));
            if ($tenants) {
                return response()->json([
                    "code" => 200,
                    "kpidata" => $tenants
                ]);
            } else {
                return response()->json([
                    "code" => 200,
                    "kpidata" => []
                ]);
            }
        } else {
            $PriorityType = \DB::select(\DB::raw("select @rownum:=@rownum+1 no, def.symbol, def.id, def.name, def.description, def.active_status, def.status,def.reject_reason,sub.name as subtenant_name, def.subtenant_id, def.child_subtenant_id, def.kpi_cat , ( select min(v.target_date) from kpi_values v, kpi_target t, mtp, fiscal_year fys, fiscal_year fye where v.kpi_target_id = t.id and t.kpi_id = def.id and v.actual_value is null and t.mtp_id = mtp.id and mtp.mtp_start = fys.id and mtp.mtp_end = fye.id and CURDATE() >= fys.start_date and CURDATE() <= fye.end_date ) next_reading_date FROM (SELECT @rownum:=0) r, kpi_def def INNER JOIN subtenant sub on sub.id = def.child_subtenant_id order by CAST(def.symbol AS UNSIGNED) ASC"));
            if ($PriorityType) {
                return response()->json([
                    "code" => 200,
                    "kpidata" => $PriorityType
                ]);
            }
        }
    }

    /** End functions added to save on change of value_period */

    public function removeKpiPermanently($id)
    {
        /**delete kpi_values_stats**/
        \DB::select(\DB::raw("delete kvs from kpi_values_stats kvs, kpi_target kt, kpi_def kd where kvs.kpi_target_id = kt.id and kt.kpi_id = kd.id and kd.status = 0 and kd.active_status = 0 and kd.id = $id"));

        /**delete kpi_values**/
        \DB::select(\DB::raw("delete kv from kpi_values kv, kpi_target kt, kpi_def kd where kv.kpi_target_id = kt.id and kt.kpi_id = kd.id and kd.status = 0 and kd.active_status = 0 and  kd.id = $id"));

        /**delete kpi_target**/
        \DB::select(\DB::raw("delete kt from kpi_target kt, kpi_def kd where kt.kpi_id = kd.id and kd.status = 0 and
    kd.active_status = 0 and kd.id = $id"));

        /**delete kpi_def**/
        if (\DB::delete(\DB::raw("delete kd from kpi_def kd where kd.status = 0 and kd.active_status = 0 and kd.id = $id"))) {
            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => "error deleting the data"
        ]);
    }

    public function kpiexceptionlist($kpiid)
    {
        $kpi_details = \DB::select(\DB::raw("select name,symbol from kpi_def where id =$kpiid"));
        $kpiexceptionlist = \DB::select(\DB::raw("select @rownum:=@rownum+1  no, ket.short_desc_en, ket.short_desc_ar,ket.long_desc_en, ket.long_desc_ar  FROM (SELECT @rownum:=0) r,kpi_exception ke, kpi_exception_type ket where
	ket.id = ke.exception_type and ke.kpi_id =$kpiid  and ke.resolve_status = 0"));
        if ($kpiexceptionlist) {
            return response()->json([
                "code" => 200,
                "kpiexception" => $kpiexceptionlist,
                "kpidetails" => $kpi_details
            ]);
        }

    }
}

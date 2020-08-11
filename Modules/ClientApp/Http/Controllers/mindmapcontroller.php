<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class mindmapcontroller extends Controller
{
    public function index()
    {

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

    public function loadSection($id)
    {

        $sectionids = \DB::select(\DB::raw("select subtenant_id from kpi_def where child_subtenant_id=$id "));

        $sectionid = $sectionids[0]->subtenant_id;
        $child_subtenant_id = $id;
        if ($sectionids) {
            return response()->json([
                "code" => 200,
                "id" => $sectionid,
                "child_subtenant_id" => $child_subtenant_id
            ]);
        }
    }


    public function mindmapdata($id, $linkflag)
    {

///////////////////////////////////////////////////////// to retrieve the sub tenant type
        $parentid = \DB::select(\DB::raw("select name as sectionname,subtenant_type_id,parent_id as parentid  from subtenant where id=$id"));
        $parent = $parentid[0]->parentid;
//       echo $parentid[0]->subtenant_type_id;
//        die();
        ///////////////////////////////////////////////
        if ($linkflag == 1) {
            $kpilink = 'newFormGenerator/1/';
        } else
            $kpilink = 'gaugechart/';

        $parentnameval = \DB::select(\DB::raw("select name from subtenant where id=$parent"));
        $parentname = $parentnameval[0]->name;

        ////subtenant_type_id==6 for section

        if ($parentid[0]->subtenant_type_id == 6) {

            $kpilist = \DB::select(\DB::raw("select kpi_def.*,tenant.name as tenantname,subtenant.name as subtenantname,process_def.name as processname from kpi_def inner join tenant on kpi_def.tenant_id=tenant.id inner join subtenant on kpi_def.subtenant_id=subtenant.id inner join process_def on kpi_def.scope_id=process_def.id where kpi_def.child_subtenant_id=$id order by kpi_def.scope_id"));

            $departmentid = \DB::select(\DB::raw("select subtenant_type_id,parent_id as deptid  from subtenant where id=$parent "));
            $department = $departmentid[0]->deptid;
            $deptidnameval = \DB::select(\DB::raw("select name from subtenant where id=$department"));
            $deptname = $deptidnameval[0]->name;
            $connection[] = array(
                'source' => $deptname,
                'target' => $parentname,

            );
            $nodes[] = array(
                'text' => $deptname,
                // 'url': 'http://www.wikiwand.com/en/الأهمية_(programming_language)',
                'fx' => 0,
                'fy' => -500,
            );


            $connection[] = array(
                'source' => $parentname,
                'target' => $parentid[0]->sectionname,

            );
            $nodes[] = array(
                'text' => $parentname,
                // 'url': 'http://www.wikiwand.com/en/الأهمية_(programming_language)',
                'fx' => 0,
                'fy' => -300,
            );
            $nodes[] = array(
                'text' => $parentid[0]->sectionname,
                // 'url': 'http://www.wikiwand.com/en/الأهمية_(programming_language)',
                'fx' => 0,
                'fy' => -100,
            );


            $processfx = -300;
            $processfy = 100;
            $kpinode = [];

            foreach ($kpilist as $key => $kpilistvalues) {
                $sectionname = \DB::select(\DB::raw("select subtenant.name as childsubtenantname,subtenant.id as id from subtenant where subtenant.id=$kpilistvalues->child_subtenant_id"));

                if (!in_array("<div id='processs' style='background-color: #dbf21f !important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>", $connection)) {

                    $connection[] = array(
                        'source' => $parentid[0]->sectionname,
                        'target' => "<div id='processs' style='background-color: #dbf21f !important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>",
                    );

                    if (!array_search("<div id='processs' style='background-color: #dbf21f !important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>", array_column($nodes, 'text'))) {

                        $kpinode = [];

                        $kpinode[] = array('text' => $kpilistvalues->id . "\t" . $kpilistvalues->name,
                            'url' => $kpilink . $kpilistvalues->id,

                            'color' => 'rgba(255, 189, 10, 1.0)');
                        $nodes[] = array(
                            'text' => "<div id='processs' style='background-color: #dbf21f !important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>",
                            'title' => 'title',
                            'fx' => $processfx,
                            'fy' => $processfy,
                            'nodes' => $kpinode

                        );
                    } else {
                        $kpinode[] = array('text' => $kpilistvalues->id . "\t" . $kpilistvalues->name,
                            'url' => $kpilink . $kpilistvalues->id,

                            'color' => 'rgba(255, 189, 10, 1.0)');

                        foreach ($nodes as $key => $val) {
                            if ($nodes[$key]['text'] == "<div id='processs'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>") {
                                $nodes[$key]['nodes'] = $kpinode;
                            }

                        }

                    }
                }

                $processfx = $processfx + 150;
                $processfy = $processfy + 50;
            }

        }

        //////////////////////////////////
         /////subtenant_type_id == 4 for department
        if ($parentid[0]->subtenant_type_id == 4) {

            $sections = array();
            $departmentid = \DB::select(\DB::raw("select name as departmentname,subtenant_type_id,parent_id as deptid  from subtenant where id=$id "));
            $department = $departmentid[0]->departmentname;
            $nodes[] = array(
                'text' => $department,
                'fx' => 0,
                'fy' => -500,
            );
            $superfx = -800;
            $supervisions = \DB::select(\DB::raw("select id,name as supervisionname from subtenant where parent_id=$id"));


            foreach ($supervisions as $key => $supervision) {
                $connection[] = array(
                    'source' => $department,
                    'target' => $supervision->supervisionname,

                );
                $nodes[] = array(
                    'text' => $supervision->supervisionname,
                    'fx' => $superfx,
                    'fy' => -300,
                );
                $superfx = $superfx + 500;
                $sections[] = \DB::select(\DB::raw("select id as sectionid,name as sectionname  from subtenant where parent_id=$supervision->id"));
                ///echo "select id as sectionid,name as sectionname  from subtenant where parent_id=$supervision->id";
                $sections[] = \DB::select(\DB::raw("select name as supervisionname  from subtenant where id=$supervision->id"));

                //echo "select name as supervisionname  from subtenant where id=$supervision->id";
               // $sections[$key]['supervisionname']=$supervision->supervisionname;

            }
//           var_dump($sections);
          // die();

            $sectionfx = -1000;

            foreach ($sections as $key => $section) {

                foreach ($section as $key => $section1) {

                    $connection[] = array(
                        'source' => $supervision->supervisionname,
                        'target' => $section1->sectionname,

                    );
                    $nodes[] = array(
                        'text' => $section1->sectionname,
                        'fx' => $sectionfx,
                        'fy' => 200,
                    );
                    $sectionfx = $sectionfx + 500;
                    $kpilist[] = \DB::select(\DB::raw("select kpi_def.*,subtenant.name as sectioname,process_def.name as processname from kpi_def  inner join subtenant on kpi_def.child_subtenant_id=subtenant.id inner join process_def on kpi_def.scope_id=process_def.id  where kpi_def.child_subtenant_id=$section1->sectionid order by kpi_def.child_subtenant_id"));

                    if (!empty($kpilist1)) {
                        $kpilist = $kpilist1;
                    }

                }
            }

            $processfx = -800;
            $processfy = -100;
            $kpinode = [];
            $processexist = [];
            if(isset($kpilist) || !empty($kpilist1)) {
                foreach ($kpilist as $key => $kpilistvalues) {

                    foreach ($kpilistvalues as $key => $kpilistvalues1) {
                        $processexist[] = $kpilistvalues1->processname . $kpilistvalues1->sectioname . $kpilistvalues1->id;

                        $connection[] = array(
                            'source' => $kpilistvalues1->sectioname,
                            'target' => "<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues1->processname . "<span id='hidespan'>$kpilistvalues1->child_subtenant_id</span>" . "</div>",

                        );

                        if (!array_search("<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues1->processname . "<span id='hidespan'>$kpilistvalues1->child_subtenant_id</span>" . "</div>", array_column($nodes, 'text'))) {

                            $kpinode = [];
                            $nodes[] = array(
                                'text' => "<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues1->processname . "<span id='hidespan'>$kpilistvalues1->child_subtenant_id</span>" . "</div>",
                                // 'url': 'http://www.wikiwand.com/en/الأهمية_(programming_language)',
                                'fx' => $processfx,
                                'fy' => 400,
                                'nodes' => $kpinode

                            );

                            $kpinode[] = array('text' => $kpilistvalues1->id . "\t" . $kpilistvalues1->name,
                                'url' => $kpilink,

                                'color' => 'rgba(255, 189, 10, 1.0)');

                        } else {

                            $kpinode[] = array('text' => $kpilistvalues1->id . "\t" . $kpilistvalues1->name,
                                'url' => $kpilink . $kpilistvalues1->id,

                                'color' => 'rgba(255, 189, 10, 1.0)');

                            foreach ($nodes as $key => $val) {
                                if ($nodes[$key]['text'] == "<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues1->processname . "<span id='hidespan'>$kpilistvalues1->child_subtenant_id</span>" . "</div>") {
                                    $nodes[$key]['nodes'] = $kpinode;
                                }

                            }

                        }


                        $processfx = $processfx + 500;
                        $processfy = $processfy + 50;

                    }
                }
            }
        }


        if ($parentid[0]->subtenant_type_id == 5) {

            $supervisions = \DB::select(\DB::raw("select id,parent_id,name as supervisionname from subtenant where id=$id"));
            $parent_id = $supervisions[0]->parent_id;

            $departmentid = \DB::select(\DB::raw("select name as departmentname  from subtenant where id=$parent_id "));
            $department = $departmentid[0]->departmentname;
            $nodes[] = array(
                'text' => $department,
                'fx' => 0,
                'fy' => -500,
            );
            $superfx = 0;

           $connection[] = array(
                'source' => $department,
                'target' => $supervisions[0]->supervisionname,

            );
            $nodes[] = array(
                'text' => $supervisions[0]->supervisionname,
                'fx' => $superfx,
                'fy' => -300,
            );

            $sectionfx = -500;

            $supervisionid = $supervisions[0]->id;
            $sections = \DB::select(\DB::raw("select id as sectionid,name as sectionname  from subtenant where parent_id=$supervisionid"));

            foreach ($sections as $key => $section) {

                $connection[] = array(
                    'source' => $supervisions[0]->supervisionname,
                    'target' => $section->sectionname,

                );
                $nodes[] = array(
                    'text' => $section->sectionname,
                    'fx' => $sectionfx,
                    'fy' => 200,
                );
                $sectionfx = $sectionfx + 800;
            }


            $kpilist1 = \DB::select(\DB::raw("select kpi_def.*,subtenant.name as sectioname,process_def.name as processname from kpi_def  inner join subtenant on kpi_def.child_subtenant_id=subtenant.id inner join process_def on kpi_def.scope_id=process_def.id  order by kpi_def.child_subtenant_id"));

            if (!empty($kpilist1)) {
                // echo "in";
                $kpilist = $kpilist1;
            }


            $processfx = -300;
            $kpinode = [];

            $processfy = 400;
            foreach ($kpilist1 as $key => $kpilistvalues) {
                if (array_search($kpilistvalues->sectioname, array_column($sections, 'sectionname')) !== false) {

                    if (!array_search("<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>", array_column($nodes, 'text'))) {
                        $kpinode = [];

                        $kpinode[] = array('text' => $kpilistvalues->id . "\t" . $kpilistvalues->name,
                            'url' => $kpilink . $kpilistvalues->id,

                            'color' => 'rgba(255, 189, 10, 1.0)');

                        $connection[] = array(
                            'source' => $kpilistvalues->sectioname,
                            'target' => "<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>",

                        );
                        $nodes[] = array(
                            'text' => "<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>",
                            'title' => 'title',
                            'fx' => $processfx,
                            'fy' => $processfy,
                            'nodes' => $kpinode

                        );
                        $processfx = $processfx + 500;
                        $processfy = $processfy + 50;


                    } else {

                        $kpinode[] = array('text' => $kpilistvalues->id . "\t" . $kpilistvalues->name,
                            'url' => $kpilink . $kpilistvalues->id,

                            'color' => 'rgba(255, 189, 10, 1.0)');

                        foreach ($nodes as $key => $val) {
                            if ($nodes[$key]['text'] == "<div id='processs' style='background-color: #dbf21f!important;'>" . $kpilistvalues->processname . "<span id='hidespan'>$kpilistvalues->child_subtenant_id</span>" . "</div>") {
                                $nodes[$key]['nodes'] = $kpinode;
                            }

                        }

                    }
                }

            }

        }
         ///// subtenant_type_id == 8 for offices
        if ($parentid[0]->subtenant_type_id == 8) {

            $parent = $parentid[0]->parentid;
            $officename = $parentid[0]->sectionname;
            $parentnameval = \DB::select(\DB::raw("select parent_id,name from subtenant where id=$parent"));
            $deptid = $parentnameval[0]->parent_id;

            $sectornameval = \DB::select(\DB::raw("select name from subtenant where id=$deptid"));

            $sectorname = $sectornameval[0]->name;
            $nodes[] = array(
                'text' => $sectorname,
                'fx' => 0,
                'fy' => -500,
            );
            $superfx = 0;

            $connection[] = array(
                'source' => $sectorname,
                'target' => $officename,

            );

            $kpifx = -300;
            $processfx = -300;
            $kpinode = [];
            $processexist = [];
            $processfy = 400;
            $kpilist = \DB::select(\DB::raw("select * from kpi_def where child_subtenant_id=$id  order by id"));
            if (isset($kpilist) || !empty($kpilist1)) {
                foreach ($kpilist as $key => $kpilistvalues) {

                    if (!array_search($officename, array_column($nodes, 'text'))) {

                        $kpinode = [];

                        $kpinode[] = array('text' => $kpilistvalues->id . "\t" . $kpilistvalues->name,
                            'url' => $kpilink . $kpilistvalues->id,

                            'color' => 'rgba(255, 189, 10, 1.0)');
                        $nodes[] = array(
                            'text' => $officename,
                            'title' => 'title',
                            'fx' => $processfx,
                            'fy' => $processfy,
                            'nodes' => $kpinode

                        );
                    } else {
                        $kpinode[] = array('text' => $kpilistvalues->id . "\t" . $kpilistvalues->name,
                            'url' => $kpilink . $kpilistvalues->id,

                            'color' => 'rgba(255, 189, 10, 1.0)');

                        foreach ($nodes as $key => $val) {
                            if ($nodes[$key]['text'] == $officename) {
                                $nodes[$key]['nodes'] = $kpinode;
                            }

                        }

                    }


                }

            }
        }
        if (isset($kpilist)) {
            $header = array(
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );

            return response()->json([
                "code" => 200,
                "data" => json_encode(array_values($connection), JSON_UNESCAPED_UNICODE),
                "nodes" => json_encode(array_values($nodes), JSON_UNESCAPED_UNICODE),
                "nameselected" => $parentid[0]->sectionname
            ]);
        }

        return response()->json([
            "code" => 404,
            "msg" => "data not found"
        ]);
    }
}

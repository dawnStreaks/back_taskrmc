<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Goutte;

class HolidayController extends Controller
{
    public function __construct()
    {
        //$this->middleware("guest");
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

       
        $holiday_info = \DB::table("holiday")
        ->join('holiday_name', 'holiday.name_id', '=', 'holiday_name.id')
        ->select('holiday.*')
        ->orderBy('sort_no', 'asc')
        ->get();


//holiday::join('holiday_name', 'holiday.name_id', '=', 'holiday_name.id') ->select('holiday.*')->orderBy('sort_no', 'asc')->get();
    
         
        return response()->json([
            "code" => 200,
            "data" => $holiday_info
        ]);
    }

    


    public function holiday_name(Request $request)
    {
        $holiday_name = \DB::select(\DB::raw("select id, name from holiday_name"));
        return response()->json([
            "code" => 200,
            "data" => $holiday_name
        ]);
    }

   

    public function allholidays(Request $request)
    {
      
        $holiday_info = \DB::select(\DB::raw("select id, name from holiday_name"));
        return response()->json([
            "code" => 200,
            "data" => $holiday_info
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function years()
    {
        $YEAR=date('Y');
        //echo $year;
        $year=array($YEAR-1,$YEAR,$YEAR+1);
        //die;
        if($year)
        {
        return response()->json([
            "code" => 200,
            "data" => $year,
        ]);
    } else {
        return response()->json([
            "code" => 404,
            "msg" => "not found"
        ]);
    }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        

        $hol_name = \DB::table("holiday_name")
        ->select('name')
        ->where('id',$request->hid)
        ->first();

//holiday_name::find($request->hid);
      
            $prctype = \DB::table("holiday")->insert(
                [
                    'name' => $hol_name->name,
                    //'name' => $request->date_from[0],
                    'hol_date' =>  $request->hol_date,
                    'year' => $request->year,
                    'name_id'=>$request->hid
                ]
            );

            
       
            if ($prctype) {
                return response()->json([
                    "code" => 200,
                    "msg" => "تم تسجيل العطلة"
                ]);
            }

            return response()->json(["code" => 400]);
        }
       public function store1(Request $request)
    {
        

        $hol_name = \DB::table("holiday_name")
        ->select('name')
        ->where('id',$request->hid)
        ->first();

//holiday_name::find($request->hid);
      
            $prctype = \DB::table("holiday")->insert(
                [
                    'name' => $hol_name->name,
                    //'name' => $request->date_from[0],
                    'hol_date' =>  $request->hol_date,
                    'year' => $request->year,
                    'name_id'=>$request->hid
                ]
            );

            
       
            if ($prctype) {
                return response()->json([
                    "code" => 200,
                    "msg" => "تم تسجيل العطلة"
                ]);
            }

            return response()->json(["code" => 400]);
        }




    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user_info = \DB::table("holiday")
        ->select('id','year','name','hol_date')
        ->where('id',$id)
        ->first();

// holiday::where("id", $id)->first();

        $user = [];
        if ($user_info) {
        
            $holi = \DB::table("holiday_name")->select('id')->where('name', $user_info->name)->first();
            $user['hid'] = $holi->id;
            $user['id'] = $user_info->id;
            $user['year'] = $user_info->year;
            $user['holiday'] = $user_info->name;
            $user['hol_date'] = $user_info->hol_date;

            return response()->json([
                "code" => 200,
                "data" => $user,
            ]);
        } else {
            return response()->json([
                "code" => 404,
                "msg" => "not found"
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

//holiday::find($id);
        $hol_name =DB::table('holiday_name')->where('id', $request->hid)->first();
// holiday_name::find($request->hid);
$update =  DB::table('holiday')->where('id', $id)
->update([
        'year' => $request->year,
        'hol_date' => $request->hol_date,
        'name'=> $hol_name->name,
        'name_id'=>$request->hid,
        ]);

       
        if ($update) {

            return response()->json([
                "code" => 200,
                "msg" => 'تم تعديل العطلة',
                // "data" => $date_hol,

            ]);

        }
        return response()->json([
            "code" => 400,
            "msg" => 'error'
        ]);
    }

    public function scrape(Request $request)
    {
        $YEAR=$request->year;//date('Y');

        $crawler = Goutte::request('GET', 'https://www.timeanddate.com/holidays/kuwait/'.$YEAR);
        
        $date  = $crawler->filter('table')->filter('tr')->each(function ($tr, $i) { return $tr->filter('th')->each(function ($th, $i) { return trim($th->text()); }); });

        $content  = $crawler->filter('table')->filter('tr')->each(function ($tr, $i) { return $tr->filter('td')->each(function ($td, $i) { return trim($td->text()); }); });
        $json =json_decode(json_encode($content, JSON_FORCE_OBJECT));
        $i=0;

        foreach( $content as  $content1) {

            foreach( $content1 as  $content2) { // print_r($json[3][0]);
//    
            $test[$i]['name']=$content1[1];
            }
            $i++;
            // var_dump($unitc);

        }
       
        $j=0;

        foreach( $date as  $date1) {

            foreach( $date1 as  $date2) {

            if(isset($date2))
            {
       $unit= $date2;
       list($day,$month) = sscanf($unit, "%d %s");
    //    print_r($month);
    $test[$j]['month']=date('m', strtotime($month));//$month;
    
    $test[$j]['day']=$day;
    }
            }
            $j++;

        }
foreach( $test as  $test1) {    
   static  $id=1;
    if(isset($test1["name"]))
        {
    $day=$test1['day'];
    $month=$test1['month'];
    // $year=$request->year;
    if($day!=NULL || $month!=NULL){
    $date_hol1 = strtotime("$YEAR-$month-$day");
    $date_hol = date("Y-m-d",$date_hol1);
    }
    else{

        $date_hol = "";

    }
    $scrap_name=$test1["name"];
    $holiday_name = \DB::select(\DB::raw("select id, name from holiday_name where name_scraping=$scrap_name"));
    $holiday_table = \DB::select(\DB::raw("DELETE FROM holiday"));

    if($holiday_table)
    {
    if($holiday_name)
    $prctype = \DB::table("holiday")->insertGetId(
    [
    'name' => $holiday_name->name,
    'hol_date'=>$date_hol,
    'year'=>$YEAR,
    'name_id'=>$holiday_name->id,
    ]
        );

        if(!$holiday_name)
    $prctype = \DB::table("holiday")->insertGetId(
    [
    'name' => $scrap_name,
    'hol_date'=>$date_hol,
    'year'=>$YEAR,
    'name_id'=>null,
    ]
        );

    }
        $id++;
    }
                        }
    if($prctype)
        return response()->json([
        "code" => 200,
    // "data" => $date_hol
        ]);

    }


    public function date(Request $request)
    {
        
       
        $date = [];

        $hol_name = DB::table('holiday_name')->where('id', $request->id)->first();
//holiday_name::find($request->id);
        
        $year=$request->year;
        
        $day=$hol_name->default_day;
        $month=$hol_name->default_month;
        $year=$request->year;
        if($day!=NULL || $month!=NULL){
        $date_hol1 = strtotime("$year-$month-$day");
        $date_hol = date("Y-m-d",$date_hol1);
        }
        else{

            $date_hol = "";

        }
        
        
        return response()->json([
            "code" => 200,
            "data" => $date_hol
        ]);
        
            

        }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $prctype = DB::table('holiday')->where('id', $id)->delete();

        if (!$prctype) {
            return response()->json([
                "code" => 404,
                "msg" => "holiday not found"
            ]);
        }

        if ($prctype) {
            return response()->json([
                "code" => 200,
                "msg" => "تم حذف العطلة"
            ]);
        }

        return response()->json(["code" => 400]);
    }
}

<?php

namespace Modules\ClientApp\Http\Controllers;

use App\Http\Helpers\Camunda;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\ProcessDefinitionSettings ;
use Modules\ClientApp\Entities\Tenant;

class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $camunda ;
    function __construct()
    {
        $this->camunda = new Camunda(env('CAMUNDA_API_URL'));
        $this->middleware('permission:process-list');
        $this->middleware('permission:process-view', ['only' => ['getProcessDefinationStatistics']]);
        //$this->middleware('permission:role-create', ['only' => ['store']]);
       // $this->middleware('permission:role-edit', ['only' => ['update']]);
       // $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $tenant = Tenant::Where('id', auth()->user()->tenant_id)->first();
        if($tenant) {
        $process = $this->camunda->getProcessDefinitions(
            [
                "tenantIdIn" => $tenant->bpm_ref ,
                "active" => true ,
                "latestVersion" => true
            ]
        ) ;

        if($process){
            return response()->json([
                "code" =>200,
                "process" => $process
            ]);
        }
        }

        return response()->json(["code"=>404, 'msg' =>  "No Process Found"]);
    }

    public function setProcessSetting(Request $request){

        $processSetting = ProcessDefinitionSettings::find($request->id);
        if(!$processSetting){
            $processSetting = ProcessDefinitionSettings::create(
                [
                    'id' => $request->id ,
                    'setting' => $request->setting
                ]
            );
        }else{
            $processSetting->update(
                [
                    'id' => $request->id ,
                    'setting' => json_encode($request->setting)
                ]
            );
        }

        if($processSetting->save()){
            return response()->json(["code"=>200]);
        }

        return response()->json(["code"=>400]);
    }

    public function getProcessSetting(Request $request){

        $tenant = Tenant::Where('id', auth()->user()->tenant_id)->first();

        $processSetting = ProcessDefinitionSettings::find($request->id);

        $tasks =  $this->camunda->getTasks(
            [
                'processDefinitionId' => $request->id,
                "tenantId" => $tenant->bpm_ref
            ]
        );

        if($processSetting){
            $processSetting->setting = json_decode($processSetting->setting,true);
            return response()->json(["code"=>200 , 'setting' => $processSetting->setting]);
        }

        return response()->json(["code"=>404]);
    }

    public function processTasks($id){
        $tenant = Tenant::Where('id', auth()->user()->tenant_id)->first();
        $tasks =  $this->camunda->getTasks(
            [
                'processDefinitionId' => $id,
                "tenantId" => $tenant->bpm_ref
            ]
        );

        if($tasks){
            return response()->json([
                "code" =>200,
                "process" => $tasks
            ]);
        }

        return response()->json(["code"=>404]);
    }

    public function startProcessInstance($key){

        $tenant = Tenant::Where('id', auth()->user()->tenant_id)->first();
        $instance = $this->camunda->startProcessInstance($key,$tenant->bpm_ref);
        if($instance){
            return response()->json([
                "code" =>200,
                "instance" => $instance
            ]);
        }

        return response()->json(["code"=>400]);
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
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id = null)
    {
        $process = $this->camunda->getSingleProcessDefinition(
                $id
        ) ;

        if($process){
            return response()->json([
                "code" =>200,
                "process" => $process
            ]);
        }

        return response()->json(["code"=>404]);
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
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }

    /**
     * Get process defination xml diagram.
     * @return Response
     */
    public function getProcessDefinationXML(Request $request){

        $diagram = $this->camunda->getBpmnXml($request->id);
        if($diagram){
            return response()->json([
                "code" =>200,
                "diagram" => $diagram
            ]);
        }

        return response()->json(["code"=>400]);
    }

    /**
     * Get the count for process definition instance
     * @return Response
     */
    public function processDefinitionCount($id) {
        $tenant = Tenant::Where('id', auth()->user()->tenant_id)->first();
        $tasks =  $this->camunda->getDefinationCount(
            [
                'processDefinitionId' => $id,
                "tenantIdIn" => $tenant->bpm_ref
            ]
        );

        if($tasks){
            return response()->json(
                $tasks
            );
        }

        return response()->json(["code"=>400]);
    }

    /**
     * Get process definition task Id
     * @return Response
    */
    public function getProcessDefinationStatistics(Request $request){

        $statics = $this->camunda->getDefinationStatistic($request->id);
        if($statics){
            return response()->json([
                "code" =>200,
                "process" => $statics
            ]);
        }

        return response()->json(["code"=>400]);
    }

    /**
     * Get process definition task Id
     * @return Response
     */
    public function getHistoryProcessDefination(Request $request){
        $tenant = Tenant::Where('id', auth()->user()->tenant_id)->first();
        $diagram = $this->camunda->getHistoryProcessDefination(
            [
                'processDefinitionId' => $request->id,
                "tenantIdIn" => $tenant->bpm_ref
            ]
        );
        if($diagram){
            return response()->json([
                "code" =>200,
                "process" => $diagram
            ]);
        }

        return response()->json(["code"=>400]);
    }

    public function getSingleProcessInstance(Request $request){

        $diagram = $this->camunda->getSingleProcessInstance($request->id
        /*[
            'processDefinitionId' => $request->id,
            "tenantIdIn" => $this->user->tenant_id
        ]*/
        );
        if($diagram){
            return response()->json([
                "code" =>200,
                "process" => $diagram
            ]);
        }

        return response()->json(["code"=>400]);
    }


    public function getProcessInstanceDetails(Request $request){


        $history = $this->camunda->getProcessinstanceHistory($request->id);

        if($history){
            $tenant = Tenant::Where('id', auth()->user()->tenant_id)->first();
            $processDefinitionId = $history->processDefinitionId;

            $tasks =  $this->camunda->getTasks(
                [
                    'processDefinitionId' => $processDefinitionId,
                    "tenantId" => $tenant->bpm_ref
                ]
            );
            $processinstancedetails = [];
            if($tasks){

                foreach ($tasks as $key => $value) {


                    if ($value->processInstanceId === $request->id) {
                        $processinstancedetails = $value ;
                    }
                }


            }

        }

        if($processinstancedetails){

            $prioritytext = \DB::table('TaskPriorityType')
                ->join('PRCType', 'TaskPriorityType.PRCType', '=', 'PRCType.IdPRCType')
                ->select('TaskPriorityType.PRCType','PRCType.Type')
                ->whereRaw('? between TypeCodeMin and TypeCodeMax', $processinstancedetails->priority)
                ->first();
            $processinstancedetails->priorityvalue = $prioritytext->Type;
            $ProcessInstanceId=$request->id;
            $ProcessInstanceDB = \DB::table('ProcessInstance')
                ->select('IdProcessInstance','created_at')
                ->where('InstanceId',$ProcessInstanceId)
                ->first();
            if(!$ProcessInstanceDB){
                $ProcessInstancecreated = date('Y-m-d', strtotime($processinstancedetails->created));
                $data = array(
                    'InstanceId'=>$request->id,
                    'created_at'=>$ProcessInstancecreated

                );

                $newProcessInstanceid=\DB::table('ProcessInstance')->insert($data);
                $ProcessInstanceIdformat = \DB::getPdo()->lastInsertId();
                $ProcessCreatedDate=date('ymd', strtotime($ProcessInstancecreated));

            }
            else {

                $ProcessInstanceIdformat = $ProcessInstanceDB->IdProcessInstance;

                $ProcessCreatedDate = date('ymd', strtotime($ProcessInstanceDB->created_at));
            }


            $processinstancedetails->InstanceIdformat=$ProcessCreatedDate.$ProcessInstanceIdformat;
            return response()->json(["code"=>200 , 'processinstancedetails' => [$processinstancedetails]]);

        }

        return response()->json(["code"=>400]);
    }

    public function getProcessActiveInstance(Request $request) {

         $diagram = $this->camunda->getActivityInstances($request->id);
        if($diagram){
            return response()->json([
                "code" =>200,
                "process" => $diagram
            ]);
        }

        return response()->json(["code"=>400]);
    }
}

<?php

namespace Modules\ClientApp\Http\Controllers;

use App\Http\Helpers\Camunda;
use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\TaskTodos;

//use Illuminate\Routing\Controller;

class TaskController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $camunda;
    function __construct()
    {
        $this->camunda = new Camunda(env('CAMUNDA_API_URL'));
        $this->middleware('permission:task-list', ['only' => ['userTasks', 'groupTasks']]);
        //$this->middleware('permission:role-view', ['only' => ['show']]);
        //$this->middleware('permission:role-create', ['only' => ['store']]);
        //$this->middleware('permission:role-edit', ['only' => ['update']]);
        //$this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {


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
    public function show()
    {
        return view('clientapp::show');
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

    public function userTasks(Request $request)
    {

        $user = auth()->user();
        $params = [
            'assignee' => $user['bpm_ref'],
            'sortBy' => 'created',
            'sortOrder' => 'asc'
        ];
        /** @var  tasks  get directly assiged tasks to this user */
        $this->tasks = $this->camunda->getTasks($params);

        //event(new NewUserTaskEvent());

        if ($this->tasks) {
            return response()->json(['code' => 200, 'tasks' => $this->tasks]);
        }

        return response()->json(['code' => 404, 'error' => "data not found"], 200);
    }

    public function groupTasks(Request $request)
    {

        $user = auth()->user();
        /*var_dump($user['id']);
        die;*/
        $groups = $this->camunda->getUserGroups($user['bpm_ref']);
        $groups = collect($groups->groups)->toArray();
        $groupsIds = [];
        foreach ($groups as $key => $value) {
            $groupsIds[] = $value->id;
        }

        $groupsIdsString = implode(',', $groupsIds);

        $params = [
            "candidateGroups" => $groupsIdsString
        ];

        /** @var  tasks  get directly assiged tasks to this user */
        $this->tasks = $this->camunda->getTasks($params);


        if ($this->tasks) {
            return response()->json(['code' => 200, 'tasks' => $this->tasks]);
        }

        return response()->json(['code' => 404, 'error' => "no tasks found"], 200);
    }

    function sortFunction( $a, $b ) {
        $t1 = strtotime($a['timestamp']);
        $t2 = strtotime($b['timestamp']);
        return $t1 - $t2;

        //return strtotime($a['timestamp']) - strtotime($b['timestamp']);
    }

   /* function array_sort_by_column(&$array, $column, $direction = SORT_ASC) {
        $reference_array = array();

        foreach($array as $key => $row) {
            $reference_array[$key] = $row[$column];
        }

        array_multisort($reference_array, $direction, $array);
    }*/

    public function taskDetails(Request $request)
    {
        $user = auth()->user();
        $this->task = $this->camunda->getSingleTask($request->id);

        $this->task->formVariable = collect($this->camunda->singleTaskFormVariable($request->id))->toArray();

        //$this->task->formRender = $this->camunda->singleTaskFormRender($request->id);

        $this->task->diagram = $this->camunda->getBpmnXml($this->task->processDefinitionId);

        $this->task->history = $this->camunda->getTaskUserOperations(['taskId' => $this->task->id, 'sortBy' => 'timestamp', "sortOrder" => 'desc']);

        $this->task->comment = $this->camunda->getCommentList($this->task->id);

        if(is_array($this->task->comment) && !empty($this->task->comment) && is_array($this->task->history) && !empty($this->task->history)) {

            $this->task->history = array_merge($this->task->history, $this->task->comment);
        } elseif (is_array($this->task->comment) && !empty($this->task->comment)) {
            $this->task->history = $this->task->comment;
        }


        $this->task->days = [];
        if ($this->task->history) {
            foreach ($this->task->history as $key => $value) {
                if(isset($value->timestamp)) {
                    $time = $value->timestamp;
                } else {
                    $time = $value->time;
                    $value->timestamp = $value->time;
                }
                $dates = gmdate("d", strtotime($time));

                $this->task->days[$dates]['timestamp'] = $time;
                $this->task->days[$dates][] = $value;
            }
        }



        $todos = TaskTodos::where('task_id', $this->task->id)->get();

        $this->task->todos = $todos;
       // usort($this->task->days, array($this,"sortFunction"));

        /*var_dump($this->task->days);
        die;*/
        /** Remove from variables */
        if (isset($this->task->formVariable['starter'])) {
            unset($this->task->formVariable['starter']);
        }

        if (count($this->task->formVariable) <= 0) {
            unset($this->task->formVariable);
        }
       // var_dump($this->task->formVariable);

        //$this->task->group = $this->camunda->getGroup(['member' => $user->bpm_ref, 'memberOfTenant' => 'riyada']);


        $this->task->identityLink = $this->camunda->getIdentityLinks($this->task->id);

        if ($this->task) {
            return response()->json(['code' => 200, 'task' => $this->task]);
        }



        return response()->json(['code' => 404, 'error' => "no tasks found"], 200);
    }

    public function cliamTask(Request $request)
    {
        $user = auth()->user();
        $this->camunda->claimTask($request->id, ["userId" => $user->bpm_ref]);

        return response()->json(['code' => 200]);
    }

    public function unClaimTask(Request $request)
    {
        $user = auth()->user();
        $this->camunda->unclaimTask($request->id);

        return response()->json(['code' => 200]);
    }

    public function addIdentityLink(Request $request) {
        $user = auth()->user();
        $addGroup = $this->camunda->addIdentityLink($request->id, ["groupId" => $request->groupId, "type" => "candidate"]);

        if($addGroup) {
            return response()->json(['code' => 200]);
        }
        return response()->json(['code' => 400]);
    }

    public function removeIdentityLink(Request $request) {
        $addGroup = $this->camunda->removeIdentityLink($request->id, ["groupId" => $request->groupId, "type" => "candidate"]);

        if($addGroup) {
            return response()->json(['code' => 200]);
        }
        return response()->json(['code' => 400]);
    }

    public function setFollowUpDate(Request $request) {

        //var_dump($request->task);
        $camundaGroupData = [];
        $followUp = ($request->followUp) ? date("Y-m-d\TH:i:s.vO", strtotime($request->followUp)) : null;
        foreach ($request->task as $key => $val) {
            if($key == 'followUp') {

                $camundaGroupData[$key] = $followUp;
            } else {
                $camundaGroupData[$key] = $val;
            }

        }

       /* $followUp = ($request->followUp) ? date("Y-m-d\TH:i:s.vO", strtotime($request->followUp)) : null;
        $camundaGroupData = [
            "name"              => $request->task['name'], //"Fill the Leave Request",
            "description"       => $request->task['description'],
            "priority"          => $request->task['priority'],
            "assignee"          => $request->task['assignee'],
            "owner"             => $request->task['owner'],
            "delegationState"   => $request->task['delegationState'],
            "due"               => $request->task['due'],
            "followUp"          => $followUp,
            "parentTaskId"      => $request->task['parentTaskId'],
            "caseInstanceId"    => $request->task['caseInstanceId'],
            'tenantId'          => $request->task['tenantId'],
        ];*/

        $SetFollowDate = $this->camunda->setFollowAndDueDate($request->task['id'], $camundaGroupData);

        if($SetFollowDate) {
            return response()->json(['code' => 200]);
        }
        return response()->json(['code' => 400]);
    }

    public function setDueDate(Request $request) {

        $dueDate = ($request->due) ? date("Y-m-d\TH:i:s.vO", strtotime($request->due)) : null;

        $camundaGroupData = [
            "name"              => $request->task['name'], //"Fill the Leave Request",
            "description"       => $request->task['description'],
            "priority"          => $request->task['priority'],
            "assignee"          => $request->task['assignee'],
            "owner"             => $request->task['owner'],
            "delegationState"   => $request->task['delegationState'],
            "due"               => $dueDate,
            "followUp"          => $request->task['followUp'],
            "parentTaskId"      => $request->task['parentTaskId'],
            "caseInstanceId"    => $request->task['caseInstanceId'],
            'tenantId'          => $request->task['tenantId'],
        ];

        $SetDueDate = $this->camunda->setFollowAndDueDate($request->task['id'], $camundaGroupData);

        if($SetDueDate) {
            return response()->json(['code' => 200]);
        }
        return response()->json(['code' => 400]);
    }

    public function submitTaskForm(Request $request){

        $params  = [] ;
        if($request->formVariable){
            foreach ($request->formVariable as $key => $value){
                //$params['variables'][$key] = ['value' => $value['value']];
                $pos = strpos($key, 'date');
                if($pos === false) {
                    $params['variables'][$key] = ['value' => $value['value']];
                } else {
                    $params['variables'][$key] = ['value' => date("d/m/Y", strtotime(date(substr($value['value'], 0, -4))))];
                }
            }
        }
        return response()->json(($this->camunda->submitTask($request->id ,$params)));
    }

    public function createComment(Request $request){
        $camundaGroupData = [
            "message"  => $request->myComment,
        ];

        $addComment = $this->camunda->createComment($request->id, $camundaGroupData);

        if($addComment) {
            return response()->json(['code' => 200]);
        }
        return response()->json(['code' => 400]);
    }

}

<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Modules\ClientApp\Entities\TaskTodos;

class TaskTodosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($taskId)
    {
        /** All Users for Authenticated user*/

        $todos = TaskTodos::where('task_id', $taskId)->get();
        //$users = $this->user->allowedUsers();
        return response()->json([
            "code" => 200,
            "data" => $todos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $taskTodos = new TaskTodos();

        $prctype = TaskTodos::create(
            [
                'task_id' => $request->taskId,
                'todo_text' => $request->todo_text
            ]
        );

        if ($prctype->save()) {
            return response()->json([
                "code" => 200,
                "msg" => "data inserted successfully"
            ]);
        }

        return response()->json(["code" => 400]);
        die;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $taskTodo = TaskTodos::Where('id', $id);
        if (!$taskTodo) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }
        $isEdit = ($request->edit) ? 'yes' : '';
        $updateTodo = TaskTodos::find($id);

        if($updateTodo->completed && $isEdit == '') {
            $message = 'undone successfully';
            $val = 0;
        } else if(!$updateTodo->completed && $isEdit == '') {
            $message = 'done successfully';
            $val = 1;
        } else if($updateTodo->completed && $isEdit == 'yes') {
            $message = 'data updated successfully';
        } else {
            $message = 'undone successfully';
        }

        if($isEdit != 'yes') {
            $updateTodo->completed = $val;
        }
        $updateTodo->todo_text = $request->todo['todo_text'];

        if ($updateTodo->save()) {
            return response()->json([
                "code" => 200,
                "msg" => $message
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => ''
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $prctype = TaskTodos::Where('id', $id);

        if (!$prctype) {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }

        if ($prctype->delete()) {
            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }

        return response()->json(["code" => 400]);
    }
}

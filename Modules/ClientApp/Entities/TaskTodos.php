<?php

namespace Modules\ClientApp\Entities;


class TaskTodos extends Model
{
    protected $fillable = ['task_id' , "todo_text"];
    protected $table = "task_todos" ;
    protected $primaryKey = "id" ;
}

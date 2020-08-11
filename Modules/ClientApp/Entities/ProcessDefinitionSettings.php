<?php

namespace Modules\ClientApp\Entities;


class ProcessDefinitionSettings extends Model
{
    protected $primaryKey = "id";
    protected $fillable = ['id' , "setting"];
    protected $table = "process_definition_setting" ;
}

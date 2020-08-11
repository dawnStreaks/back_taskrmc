<?php

namespace Modules\ClientApp\Entities;
use OwenIt\Auditing\Contracts\Auditable;

class PriorityType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "TaskPriorityType" ;
    protected $primaryKey = "idTaskPriorityType" ;

    protected $auditInclude = ['TypeCodeMin' , "TypeCodeMax","PRCType"];

    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];
}

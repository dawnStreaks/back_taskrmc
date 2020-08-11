<?php

namespace Modules\ClientApp\Entities;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class PRCType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;


    protected $table = "PRCType" ;
    protected $primaryKey = "idPRCType" ;
    protected $fillable = ['TypeCode' , "Type"];
    protected $auditInclude = ['TypeCode' , "Type"];

    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];

}

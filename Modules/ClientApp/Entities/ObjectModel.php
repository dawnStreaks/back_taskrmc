<?php namespace Modules\ClientApp\Entities;
use OwenIt\Auditing\Contracts\Auditable;
class ObjectModel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['name'];
    protected $table = "object_model";
    protected $primaryKey = "id";

    protected $auditInclude = [
        'name'
    ];

    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];

}

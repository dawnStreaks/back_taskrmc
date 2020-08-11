<?php

namespace Modules\ClientApp\Entities;
use OwenIt\Auditing\Contracts\Auditable;


class SubTenant extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    protected $table = "subtenant";
    protected $primaryKey = "id" ;
    protected $fillable = ['name', 'description', 'subtenant_type_id' , 'tenant_id' , 'bpm_ref' , 'parent_id'];

    public function tree()
    {
        return $this->hasMany(SubTenant::class,'parent_id')->with('tree');
    }
    public function children()
    {
        return $this->hasMany(SubTenant::class,'parent_id')->select(['id', 'name as label','parent_id']);
    }

    public function children1()
    {
        return $this->hasMany(SubTenant::class,'parent_id')->select(['id', 'name as label','parent_id']);
    }

    protected $auditInclude = [
        'name', 'description', 'subtenant_type_id' , 'tenant_id' , 'bpm_ref' , 'parent_id'
    ];
    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];


}

<?php

namespace Modules\ClientApp\Entities;
use OwenIt\Auditing\Contracts\Auditable;

class Groups extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['name' , 'description', 'tenant_id' , 'bpm_ref'] ;
    protected $table = "groups" ;

    /*public function SubTenant(){
        return $this->hasOne(SubTenant::class , 'id' , 'subtenant_id');
    }*/
   /* public function Type(){
        return $this->hasOne(TenantUserType::class , 'id' , 'tenant_user_type_id');
    }*/
    protected $auditInclude = [
        'name' , 'description', 'tenant_id' , 'bpm_ref'
    ];

    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];

    public function usersgroup()
    {
        return $this->hasMany(UserGroup::class, 'group_id', 'id');
    }
}

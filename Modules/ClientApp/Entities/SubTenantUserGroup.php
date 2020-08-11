<?php

namespace Modules\ClientApp\Entities;

class SubTenantUserGroup extends Model
{
    protected $fillable = ['name' , 'description', 'tenant_id' , 'subtenant_id', 'bpm_ref'] ;
    protected $table = "subtenant_user_group" ;
    protected $dates = ['deleted_at'];

    public function SubTenant(){
        return $this->hasOne(SubTenant::class , 'id' , 'subtenant_id');
    }
    public function Type(){
        return $this->hasOne(TenantUserType::class , 'id' , 'tenant_user_type_id');
    }


}

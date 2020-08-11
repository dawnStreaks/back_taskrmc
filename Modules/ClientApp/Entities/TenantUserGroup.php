<?php

namespace Modules\ClientApp\Entities;

class TenantUserGroup extends Model
{
    protected $fillable = ['name' , 'desc', 'idTenants' , 'idSubTenant'] ;
    protected $table = "TenantUserGroup" ;
    protected $dates = ['deleted_at'];

    public function SubTenant(){
        return $this->hasOne(SubTenant::class , 'idSubTenant' , 'idSubTenant');
    }
    public function Type(){
        return $this->hasOne(TenantUsersType::class , 'id' , 'TenantUsersType_id');
    }


}

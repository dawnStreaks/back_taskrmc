<?php

namespace Modules\ClientApp\Entities;

class TenantUserType extends Model
{
    protected $table = "tenant_user_type" ;
    protected $primaryKey = "id" ;
    protected $fillable = ['name' , 'description' , 'tenant_id'];
}

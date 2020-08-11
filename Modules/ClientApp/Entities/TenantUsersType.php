<?php

namespace Modules\ClientApp\Entities;

class TenantUsersType extends Model
{
    protected $table = "TenantUsersType" ;
    protected $primaryKey = "id" ;
    protected $fillable = ['name' , 'desc' , 'idTenants'];
}

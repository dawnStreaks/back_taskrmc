<?php

namespace Modules\ClientApp\Entities;

class TenantUsers extends Model
{

    protected $table = "TenantUsers" ;
    protected $fillable = ['UserBPMRef', 'user_id', 'idTenantUsersType' , "idTenants"];


//    public function groupAuth(){
//        return $this->belongsToMany(GroupSubTenants::class,'UserGroupsAuth','TenantUsers_id','idGroupSubTenants');
//    }
//
//    public function authData(){
//        return $this->hasOne(\App\User::class,'id','user_id')->where('user_type',3)->with('translations');
//    }
//
//    public function group(){
//        return $this->belongsTo(TenantUserGroup::class , 'TenantUserGroup_id' , 'id')->with('translations') ;
//    }
//
//    public function type(){
//        return $this->belongsTo(TenantUsersType::class , 'idTenantUsersType' , 'id')->with('translations') ;
//    }




}

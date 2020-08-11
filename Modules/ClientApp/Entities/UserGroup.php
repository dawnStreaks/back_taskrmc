<?php

namespace Modules\ClientApp\Entities;

use Modules\ClientApp\User;

class UserGroup extends Model
{
    protected $fillable = ['user_id' , 'group_id'] ;
    protected $table = "user_group" ;

    /*public function Groups(){
        return $this->hasMany(Groups::class , 'id' , 'group_id');
    }
    public function Users(){
        return $this->hasOne(User::class , 'id' , 'user_id');
    }*/


}

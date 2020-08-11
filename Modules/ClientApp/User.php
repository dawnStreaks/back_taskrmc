<?php

namespace Modules\ClientApp;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\MailResetPasswordToken;
use App\Http\Helpers\Camunda ;
use Modules\ClientApp\Entities\UserGroup;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\JWTAuth;
use Modules\ClientApp\Entities\Groups ;
use Modules\ClientApp\Entities\SubTenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;



//use Tymon\JWTAuth\Contracts\JWTSubject;
//use Illuminate\Notifications\Notifiable;
//use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject,Auditable


{
    use \OwenIt\Auditing\Auditable;

    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    //protected $table = "users";

    protected $guard_name = 'api'; // or whatever guard you want to use

    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', "second_name" , "last_name" ,'email', 'password', 'default_password' , "activation_code" , "isActive" , "status" , "user_type", 'filename', 'bpm_ref', 'user_type', 'subtenant_id'
    ];
    protected $auditInclude = [
        'name', "second_name" , "last_name" ,'email', 'password', 'default_password' , "activation_code" , "isActive" , "status" , "user_type", 'filename', 'bpm_ref', 'user_type', 'subtenant_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'mime', 'original_filename'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordToken($token));
    }

    /*public function permissions(){
        $group = Groups::Where('id', \JWTAuth::parseToken()->authenticate()->subtenant_user_group_id)->first();
        $camunda = new Camunda(env('CAMUNDA_API_URL'));
        $permissions = $camunda->getAuthorizations([
            'groupIdIn' => $group->bpm_ref
        ]);
        foreach ($permissions as $key => $value){
            $allowedPermissions[$value->resourceId] = $value->permissions ;
        }
        return $allowedPermissions ;
    }*/

    /**
     * Resources type IDS
     * 0 Apps
     * 1 Users
     * 2 Group
     * 3 Group Membership
     * 4 Authorizations
     * 6 Process Definition
     * 7 Tasks
     */

    public function groups()
    {
        return $this->hasManyThrough(Groups::class, UserGroup::class, 'user_id', 'id', 'id', 'group_id');
    }

    public function hasAccess($permission = null , $resourceType = null , $resourceId = null){
        $allowedPermissions = [] ;

        $PriorityType = \DB::table('groups')
            ->join('user_group', 'user_group.group_id', '=', 'groups.id')
            ->select('groups.bpm_ref')
            ->where('user_group.user_id', \JWTAuth::parseToken()->authenticate()->id)
            ->get();

        $allBpmRef = [];
        foreach ($PriorityType as $key => $val) {
            $allBpmRef[$key] = $val->bpm_ref;
        }

        $bpm_ref = (implode(",",$allBpmRef));

       // $group = Groups::Where('id', \JWTAuth::parseToken()->authenticate()->subtenant_user_group_id)->first();

        $camunda = new Camunda(env('CAMUNDA_API_URL'));
        $groupPermissions = $camunda->getAuthorizations([
            'groupIdIn' => $bpm_ref,
            'resourceType' =>  $resourceType
        ]);
        $userPermissions = $camunda->getAuthorizations([
            'userIdIn' => \JWTAuth::parseToken()->authenticate()->bpm_ref,
            'resourceType' =>  $resourceType
        ]);

        if($groupPermissions){
            foreach ($groupPermissions as $key => $value){

                if(isset($resourceId)){
                    if(
                        (in_array(strtoupper($permission) , $value->permissions) OR in_array("ALL" , $value->permissions))
                        AND
                        ($value->resourceId == $resourceId OR $value->resourceId == "*" OR $value->resourceId == "ALL")
                    ){
                        return true ;
                    }
                }elseif(!$resourceId){
                    if(in_array(strtoupper($permission) , $value->permissions) OR in_array("ALL" , $value->permissions)){
                        return true ;
                    }
                }
            }
        }

        if($userPermissions){
            foreach ($userPermissions as $key => $value){
                if(isset($resourceId)){
                    if(
                        (in_array(strtoupper($permission) , $value->permissions) OR in_array("ALL" , $value->permissions))
                        AND
                        ($value->resourceId == $resourceId OR $value->resourceId == "*" OR $value->resourceId == "ALL")
                    ){
                        return true ;
                    }
                }elseif(!$resourceId){
                    if(in_array(strtoupper($permission) , $value->permissions) OR in_array("ALL" , $value->permissions)){
                        return true ;
                    }
                }
            }
        }

        return false ;
    }

    public function allowedGroups(){
        $tenantsIDs = $this->allowedTenants();

        return $group = DB::table('users')
            ->join('user_group', 'users.id', '=', 'user_group.user_id')
            ->join('groups', 'groups.id', '=', 'user_group.group_id')
            ->select('groups.id')
            ->whereIn("users.subtenant_id",$tenantsIDs->pluck('id'))
            ->get();

        //return $groups = Groups::where('tenant_id',$this->tenant_id)
        //    ->whereIn("subtenant_id",$tenantsIDs->pluck('id'))
          //  ->get();
    }
    public function allowedTenants(){
        /*$group = Groups::where([
            'tenant_id' => $this->tenant_id ,
            'id' => $this->subtenant_user_group_id
        ])->first();*/

       $user = User::Where([
            'tenant_id' => $this->tenant_id ,
            'id' => \JWTAuth::parseToken()->authenticate()->id
        ])->first();


        $allowedSubTenants = SubTenant::where([
            'tenant_id' => $this->tenant_id,
            "parent_id" => $user->subtenant_id
        ])->with('tree')->get();

        /*echo "<pre>";
        print_r($allowedSubTenants);*/
        $tenantsIDs = $this->eachTree($allowedSubTenants) ;
        $tenantsIDs[] = $user->subtenant_id ; /** Add the subtenant of user */
        if($tenantsIDs != false){
            return $userTenants = SubTenant::whereIn("id" , $tenantsIDs)->get();
        }
        return null ;
    }

    public function is($roleName)
    {
        foreach ($this->roles()->get() as $role)
        {
            if ($role->name == $roleName)
            {
                return true;
            }
        }

        return false;
    }

    public function allowedUsers(){

        $tenantsIDs = $this->allowedTenants();
       /* var_dump($this->allowedGroups()->pluck('id'));
        die;*/

        $users = DB::table('users')
            //->join('user_group', 'users.id', '=', 'user_group.user_id')
            //->join('groups', 'groups.id', '=', 'user_group.group_id')
            ->join('subtenant', 'subtenant.id', '=', 'users.subtenant_id')
            ->select('users.*', 'subtenant.name as subname')
            ->where('users.tenant_id' , $this->tenant_id)
            ->where('users.deleted_at' , null)
            ->orderBy('users.id', 'desc');
        /*if(!$this->is('Admin')) {
            $tenantsIDs = $this->allowedTenants();
            $users = $users->whereIn('users.subtenant_id', $tenantsIDs->pluck('id'));
        }*/

        $users = $users->distinct()->get();

            return $users;


       // return  $users = User::where('tenant_id' , $this->tenant_id)->whereIn('subtenant_user_group_id' , $this->allowedGroups()->pluck('id'))->get();
    }

    public function hasTenantAccess($tenantID){
        if($this->allowedTenants() != null){
            if(in_array($tenantID , collect($this->allowedTenants()->pluck('subtenant_id'))->toArray())){
                return true ;
            }
        }
        return false ;
    }

    public function hasGroupAccess($groupID){
        if($this->allowedGroups() != null){
            if(in_array($groupID , collect($this->allowedGroups()->pluck('id'))->toArray())){
                return true ;
            }
        }
        return false ;
    }

    public function hasUserAccess($userID){
        if($this->allowedGroups() != null){
            if(in_array($userID , collect($this->allowedUsers()->pluck('id'))->toArray())){
                return true ;
            }
        }
        return false ;
    }

    public function eachTree($array){
        $result = [] ;
        if ((is_array($array) OR is_object($array))) {
            $array = collect($array)->toArray();
            foreach ($array as $key => $value){
                if(isset($value['id'])){
                    $result[] = $value['id'];
                }

                if(isset($value['tree']) AND !empty($value['tree'])){
                    $result[] = $this->eachTree($value['tree']);
                }
            }
        }
        if(isset($result) AND !empty($result)){
            return array_flatten($result) ;
        }
        return false ;
    }
}

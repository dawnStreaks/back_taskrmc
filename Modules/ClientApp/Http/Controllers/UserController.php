<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Modules\ClientApp\Entities\Groups;
use Modules\ClientApp\Entities\SubTenant;
use Modules\ClientApp\Entities\SubTenantUserGroup;
use Modules\ClientApp\Entities\Tenant;
use Modules\ClientApp\Entities\TenantUserType;
use Modules\ClientApp\Entities\UserGroup;
use Modules\ClientApp\User;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\UrlGenerator;
/** Validations */

use Illuminate\Support\Facades\Validator;
use Modules\ClientApp\Http\Requests\UserStore;
use Modules\ClientApp\Http\Requests\UserUpdate;
use Modules\ClientApp\Http\Requests\UserProfileUpdate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        /** All Users for Authenticated user*/
        $users = $this->user->allowedUsers();
        return response()->json([
            "code" => 200,
            "data" => $users
        ]);
    }

    public function loadKpiSectorUsers($sector, $orgUnit=null) {
        if($sector != 'null') {
        $users = \DB::table('users')
            ->join('subtenant', 'subtenant.id', '=', 'users.subtenant_id')
            ->select('users.*', 'subtenant.name as subname')
            ->where('users.tenant_id' , $this->user->tenant_id)
            ->where('users.sector_id', '=', $sector)
            ->where('users.deleted_at' , null)
            ->orderBy('users.id', 'desc');

        $users = $users->distinct()->get();
        } else {
            $users = \DB::table('users')
                ->join('subtenant', 'subtenant.id', '=', 'users.subtenant_id')
                ->select('users.*', 'subtenant.name as subname')
                ->where('users.tenant_id' , $this->user->tenant_id)
                ->where('users.deleted_at' , null)
                ->orderBy('users.id', 'desc');
            $users = $users->distinct()->get();
        }
        return response()->json([
            "code" => 200,
            "data" => $users
        ]);
    }
    public function loadKpiOrgUsers($orgUnit, $sector =null) {
        if($orgUnit != 'null') {

        $users = \DB::select(\DB::raw("WITH RECURSIVE cte (level1_id, id, parent_id, subtenant_type, name, path) AS (
	-- This is end of the recursion: Select low level
	select id, id, parent_id, subtenant_type_id, name, concat( cast(id as char(200)), '_')
		from subtenant where
        id = $orgUnit -- set your arg here
	UNION ALL
    -- This is the recursive part: It joins to cte        
    select c.level1_id, s.id, s.parent_id, s.subtenant_type_id, s.name, CONCAT(c.path, ',', s.id)
		from subtenant s
        inner join cte c on s.parent_id = c.id
	)
	-- select id, name, subtenant_type, parent_id
	--  cte.level1_id, cte.id, cte.parent_id, cte.subtenant_type,
	select cte.name as subname, u.*
	from cte, users u where
	u.subtenant_id = cte.id
	order by path, u.name;"));
        } else {
            $users = \DB::table('users')
                ->join('subtenant', 'subtenant.id', '=', 'users.subtenant_id')
                ->select('users.*', 'subtenant.name as subname')
                ->where('users.tenant_id' , $this->user->tenant_id)
                ->where('users.sector_id', '=', $sector)
                ->where('users.deleted_at' , null)
                ->orderBy('users.id', 'desc');
            $users = $users->distinct()->get();
        }
        return response()->json([
            "code" => 200,
            "data" => $users
        ]);
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(UserStore $request)
    {

        /*var_dump($request->subtenant_user_group_id);
        die;
        */
        //$defaultPassword = uniqid(rand());
        $defaultPassword = 'rmc123';

        $splitName = explode('@', $request->email, 2);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->default_password = bcrypt($defaultPassword);
        $user->password = $user->default_password;
        $user->status = $request->status;
        $user->second_name = $request->second_name;
        $user->last_name = $request->last_name;
        $user->phone_internal = $request->phone_internal;
        //$user->idTenants = $this->user->idTenants;
        $user->tenant_id = $this->user->tenant_id;
        $user->subtenant_id = $request->subtenant_id;
        $user->sector_id = $request->sector_id;
        $user->user_type = 1;
        //$user->TenantUserGroup_id = $request->TenantUserGroup_id;
        //$user->subtenant_user_group_id = $request->subtenant_user_group_id;
        $user->bpm_ref = $splitName[0];

        /*foreach ($request->subtenant_user_group_id as $groupVal) {
            var_dump($groupVal);
        }
        var_dump($groupVal);
        die;*/

        if ($user->save()) {
            $user->assignRole($request->input('user_type'));
            /** @var $camundaUserData - set camunda user data of stored user */
            $camundaUserData = [
                "profile" => [
                    "id" => (string)$splitName[0],
                    "firstName" => $user->name,
                    "lastName" => $user->last_name,
                    "email" => $user->email
                ],
                "credentials" => [
                    "password" => $user->password
                ]
            ];

            // Create Camunda user

            if ($this->validateFunction($this->camunda->createSingleUser($camundaUserData))) {

                foreach ($request->subtenant_user_group_id as $groupVal) {
                    $userGroup = new UserGroup;
                    $userGroup->user_id = $user->id;
                    $userGroup->group_id = $groupVal;
                    if ($userGroup->save()) {
                        $group = Groups::Where('id', $groupVal)->first();
                        //var_dump($group->bpm_ref.'===='.$user->bpm_ref);
                        //die;
                        /** Assign user to group */
                        $this->camunda->addGroupMember($group->bpm_ref, $user->bpm_ref);
                    }
                }

                //               die;
                $tenant = Tenant::Where('id', $user->tenant_id)->first();
                /** Assign user to tenant */
                $this->camunda->addTenantMember((string)$tenant->bpm_ref, $user->bpm_ref);
                return response()->json([
                    "code" => 200,
                    "msg" => "data inserted successfully",
                    "default Password is : " => $defaultPassword
                ]);
            } else {
                $user->delete();
            }
        }

        return response()->json([
            "code" => 400,
            "msg" => "error saving the data"
        ]);

    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $validator = Validator::make(["id" => $id], [
            'id' => "required|integer"
        ]);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['code' => 400, 'errors' => $validator->errors()]);
        }

        $user = User::where("tenant_id", \Auth::user()->tenant_id)->where("id", $id)->first();

        $groupIds = UserGroup::select('group_id')->Where("user_id", $id)->get();
        $groupsAll = [];
        $i = 0;
        foreach ($groupIds as $key => $val) {
            $groupsAll[$i] = $val->group_id;
            $i++;
        }

        $user->subtenant_user_group_id = $groupsAll;
        if ($user) {
            //$user = User::find($id);
            $userRole = $user->roles->pluck('name')->all();
            $user->user_type = $userRole;
            //$userRole = $user->roles->get();

            /*var_dump($userRole);
            die;*/
            return response()->json([
                "code" => 200,
                "data" => $user,
                //"roles" => $userRole
            ]);
        } else {
            return response()->json([
                "code" => 404,
                "msg" => "data not found"
            ]);
        }
    }

    public function getApps() {
        $checkRoles = \DB::select(\DB::raw("select * from check_roles_group"));
        if ($checkRoles) {
            return response()->json([
                "code" => 200,
                "data" => $checkRoles
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => "data not found"
        ]);
    }
    public function test123() {
        echo 'dddd';
    }
    public function authUser(Request $request, $id= null)
    {
        if($id != 'null') {
            $vals = (explode("-", $id));
            $id = $vals[1];
        }
        $url = explode("/api/", url()->current());
        $this->user->url = $url[0];

        if ($this->user) {
            //$userType = TenantUserType::find($this->user->user_type);
            //$this->user->roles = $userType->name;
            $this->user->roles = auth()->user()->getRoleNames();
        }

        $userPremissions = [];

        $checkRoles = \DB::select(\DB::raw("SELECT app.id, model.name FROM check_roles_group app INNER JOIN app_objects obj on obj.app_id=app.id INNER JOIN object_model model on model.id= obj.object_id WHERE app.id=$id"));
        //var_dump($checkRoles);

        $permissions = Permission::all();
        if ($permissions) {
            //$userGroupPremissions = [];
            foreach (Permission::all() as $permission) {
                if (Auth::user()->can($permission->name)) {

                    if($checkRoles) {
                        foreach ($checkRoles as $permissionGrp) {
                            //echo $permission->name."===".$permissionGrp->routes.PHP_EOL;
                            if (strpos($permission->name, $permissionGrp->name) !== false) {
                                //echo 'inn';
                                //$userGroupPremissions[] = $permissionGrp->routes;
                                $userPremissions[] = $permission->name;
                            }
                        }
                    }
                }
            }
        }
        $this->user->allPermissions = $userPremissions;
        //$this->user->userGroupPremissions = $userGroupPremissions;
        return response()->json([
            "code" => 200,
            "data" => $this->user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('clientapp::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(UserUpdate $request)
    {
        $user = User::find($request->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = $request->status;
        $user->second_name = $request->second_name;
        $user->last_name = $request->last_name;
        $user->last_name = $request->last_name;
        $user->phone_internal = $request->phone_internal;
        //$user->idTenants = \Auth::user()->idTenants;
        $user->tenant_id = \Auth::user()->tenant_id;
        $user->subtenant_id = $request->subtenant_id;
        $user->sector_id = $request->sector_id;
        //$user->user_type = 1;
        //$user->TenantUserGroup_id = $request->TenantUserGroup_id;
        //$user->subtenant_user_group_id = $request->subtenant_user_group_id;

        if ($user->save()) {

            \DB::table('model_has_roles')->where('model_id', $request->id)->delete();


            $user->assignRole($request->input('user_type'));

            $dddd = $this->camunda->getGroupMember(['member' => $user->bpm_ref]);
            $userdddd = UserGroup::Where('user_id', $user->id);
            if ($userdddd->forceDelete()) {

                foreach ($dddd as $kd => $dd) {
                    $this->camunda->deleteGroupMember($dd->id, $user->bpm_ref);
                }
                foreach ($request->subtenant_user_group_id as $groupVal) {
                    $userGroup = new UserGroup();
                    $userGroup->user_id = $user->id;
                    $userGroup->group_id = $groupVal;
                    $userGroup->save();
                    $group = Groups::Where('id', $groupVal)->first();
                    //$tenant = Tenant::Where('id', $user->tenant_id)->first();

                    /** Assign user to group */
                    $this->camunda->addGroupMember($group->bpm_ref, $user->bpm_ref);
                }

            }

            $camundaUserData = [
                "id" => (string)\JWTAuth::parseToken()->authenticate()->bpm_ref,
                "firstName" => $request->name,
                "lastName" => $request->last_name,
                "email" => $request->email
            ];
            if ($this->validateFunction($this->camunda->updateUserProfile(\JWTAuth::parseToken()->authenticate()->bpm_ref, $camundaUserData))) {
                return response()->json([
                    "code" => 204,
                    "msg" => "data updated successfully"
                ]);
            }
        }

        return response()->json([
            "code" => 400,
            "msg" => "error updating the data"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $validator = Validator::make(["id" => $id], [
            'id' => "required|integer"
        ]);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(['code' => 400, 'errors' => $validator->errors()]);
        }
        $user = User::find($id);
        if ($user->delete()) {
            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }

        return response()->json([
            "code" => 400,
            "msg" => "error deleting the data"
        ]);
    }

    public function getProfile($id)
    {
        if (\JWTAuth::parseToken()->authenticate()->id) {
            $validator = Validator::make(["id" => $id], [
                'id' => "required|integer"
            ]);
            if ($validator->fails()) {
                //pass validator errors as errors object for ajax response
                return response()->json(['code' => 206, 'errors' => $validator->errors()]);
            }
            if (\JWTAuth::parseToken()->authenticate()->id == $id) {
                $user = User::find($id);
                if ($user) {
                    $url = explode("/api/", url()->current());
                    $user->url = $url[0];
                    if ($this->validateFunction($this->camunda->getUserProfile($user->bpm_ref))) {
                        /** Assign user to group */
                        $userData = $this->camunda->getUserProfile($id);
                        /** Assign user to tenant */
                        //$this->camunda->addTenantMember((string)$user->tenant_id, $user->id);
                        return response()->json([
                            "code" => 200,
                            "data" => $user
                        ]);
                    }
                }
            }
        }

        return response()->json([
            "code" => 400,
            "msg" => "error user not logged in"
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function updateProfile(Request $request)
    {
        if (\JWTAuth::parseToken()->authenticate()) {

            $user = User::find(\JWTAuth::parseToken()->authenticate()->id);
            if ($user) {

                $rules = array(
                    'image' => 'mimes:jpeg,jpg,png,gif|required|max:10000' // max 10000kb
                );

                $fileArray = array('image' => $request->image);
                if ($request->image) {
                    // Now pass the input and rules into the validator
                    $validator = Validator::make($fileArray, $rules);

                    // Check to see if validation fails or passes
                    if ($validator->fails()) {
                        return response()->json([
                            "code" => 404,
                            "msg" => 'Please Select jpeg,jpg,png,gif or Image side less then 1MB'
                        ]);
                    }
                    if (!empty($user->file_name)) {
                        $file_path = explode('/', $user->file_name);
                        $fileName = end($file_path);
                        if (file_exists(public_path('images') . '/' . $fileName)) {
                            unlink(public_path('images') . '/' . $fileName);
                        }
                    }
                    // var_dump($request->name);
                    $imageName = $user->name . '.' . strtoupper($request->image->getClientOriginalExtension());
                    if ($request->image->move(public_path('images'), $imageName)) {
                        $user->file_name = $imageName;
                    }
                }
                $user->name = $request->name;
                $user->email = $request->email;
                $user->last_name = $request->last_name;
                $user->second_name = $request->second_name;

                if ($user->save()) {
                    $camundaUserData = [
                        "id" => (string)\JWTAuth::parseToken()->authenticate()->bpm_ref,
                        "firstName" => $request->name,
                        "lastName" => $request->last_name,
                        "email" => $request->email
                    ];
                    if ($this->validateFunction($this->camunda->updateUserProfile(\JWTAuth::parseToken()->authenticate()->bpm_ref, $camundaUserData))) {
                        return response()->json([
                            "code" => 204,
                            "msg" => "data updated successfully"
                        ]);
                    }
                }
            }
        }
        return response()->json([
            "code" => 404,
            "msg" => "error user not logged in"
        ]);
    }

    public function changeuserPassword(Request $request)
    {
        if (\JWTAuth::parseToken()->authenticate()) {
            $user = User::find($request->id);

            if ($user) {
                $user->password = Hash::make($request->password);
                if (Hash::check($request->authenticatedUserPassword, \JWTAuth::parseToken()->authenticate()->password)) {
                    if ($user->save()) {

                        $updatePassword = $this->camunda->updateUserCredentials(\JWTAuth::parseToken()->authenticate()->bpm_ref,
                            [
                                "password" => $request->password,
                                "authenticatedUserPassword" => $request->authenticatedUserPassword
                            ]
                        );

                        if ($updatePassword) {
                            return response()->json([
                                "code" => 200,
                                "msg" => "data updated successfully"
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        "code" => 404,
                        "msg" => "error updating the data"
                    ]);
                }

            }
        }
        return response()->json([
            "code" => 404,
            "msg" => "error user not logged in"
        ]);
    }

    public function getRoleList(Request $request) {
        $roles = Role::get();

        return response()->json([
            "code" => 200,
            "roles" => $roles
        ]);
    }

    public function getGroups(Request $request)
    {
        $user_groups = Groups::where('tenant_id' , auth()->user()->tenant_id)->get();

        if($user_groups){
            return response()->json([
                "code" =>200,
                "groups" => $user_groups
            ]);
        }

        return response()->json(["code"=>400]);
    }

    public function getTenants()
    {

        $tenants = SubTenant::with('tree')->orWhere('parent_id' , 0)->orWhere('parent_id',null)->where('tenant_id',\Auth::user()->tenant_id)->get();

        if($tenants){
            return response()->json([
                "code" =>200,
                "subTenants" => $tenants
            ]);
        }

        return response()->json(["code"=>400]);
    }

    public function getsectorg($id)
    {

        $user = User::Where([

            'id' => $id
        ])->first();

        $sectiondata =  \DB::table('subtenant')
             ->select('name as sectorname')
             ->where('id' , $user->sector_id)
            ->first();
        $orgdata =  \DB::table('subtenant')
             ->select('name as orgunit')
             ->where('id' , $user->subtenant_id)
            ->first();



        if($user){
            return response()->json([
                "code" =>200,
                "sectorname" => $sectiondata->sectorname ,
                "orgunit" => $orgdata->orgunit ,

            ]);
        }

        return response()->json(["code"=>400]);
    }
}

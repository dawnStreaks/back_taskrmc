<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Modules\ClientApp\Entities\Groups;
use Illuminate\Support\Facades\DB;
use Modules\ClientApp\Entities\ObjectModel;
use Modules\ClientApp\Http\Requests\GroupStore;
use Modules\ClientApp\Http\Requests\GroupUpdate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RolesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:role-list');
        $this->middleware('permission:role-view', ['only' => ['show']]);
        $this->middleware('permission:role-create', ['only' => ['store']]);
        $this->middleware('permission:role-edit', ['only' => ['update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /*public function getModels($path){
        $out = [];
        $results = scandir($path);
        foreach ($results as $result) {
            if ($result === '.' or $result === '..') continue;
            $filename = $path . '/' . $result;
            if (is_dir($filename)) {
                $out = array_merge($out, $this->getModels($filename));
            }else{
                //$out[] = substr($filename,0,-4);
                if( strpos( $filename, '.git' ) == false) {
                    $out[] = substr($filename, 56, -4);
                }
            }
        }
        return $out;
    }*/
    public function index(Request $request)
    {

        /* $tables_in_db = \DB::select('SHOW TABLES');
         $db = "Tables_in_".env('DB_DATABASE');
         $tables = [];
         foreach($tables_in_db as $table){
             $tables[] = $table->{$db};
         }
         print_r($tables);
     echo $path = base_path('Modules/ClientApp/Entities');//"Modules\\ClientApp\\";
 //die;

     dd($this->getModels($path));*/

        $roles = Role::orderBy('id', 'DESC')->get();
        if ($roles) {
            //$role = Role::create(['name' => 'fsdfsfdsf', 'guard_name' => 'api']);
            return response()->json([
                "code" => 200,
                "roles" => $roles
            ]);
        }
        return response()->json(["code" => 400]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $role = Role::save(['name' => $request->input('name'), 'guard_name' => 'api']);
        $role->syncPermissions($request->input('permission'));

        return response()->json([
            "code" => 200,
            "msg" => "data inserted successfully"
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = \DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        if ($role) {

            $permission = Permission::whereIn('id', $rolePermissions)->pluck('name')->all();
            return response()->json([
                "code" => 200,
                "data" => $role,
                "permissionId" => array_values($rolePermissions),
                "permissionVal" => $permission
            ]);
        }

        return response()->json([
            "code" => 404,
            "msg" => "data not found"
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        $role->name = $request->input('name');

        if ($role->save()) {


            $role->syncPermissions($request->input('permission'));

            return response()->json([
                "code" => 200,
                "msg" => "data updated successfully"
            ]);
        }


        return response()->json(["code" => 400]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (\DB::table("roles")->where('id', $id)->delete()) {
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

    public function getPermissions(Request $request)
    {
        $permissions = Permission::get();
        if ($permissions) {
            //$role = Role::create(['name' => 'fsdfsfdsf', 'guard_name' => 'api']);
            return response()->json([
                "code" => 200,
                "permissions" => $permissions
            ]);
        }
        return response()->json(["code" => 400]);
    }

    public function getRoleObject(Request $request)
    {
        $objectModel = ObjectModel::get();
        $new = [];
        foreach ($objectModel as $key => $value) {

            //var_dump($value->name);
            $permissions = Permission::Where('name', 'like', '' . $value->name . '%')->get();
            $new[$value->name] = $permissions;

        }
        return response()->json([
            "code" => 200,
            "objectModel" => $new
        ]);
    }
}

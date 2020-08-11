<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User ;
use Adldap\Laravel\Facades\Adldap;
use App\Http\Controllers\Auth\ForgotPasswordController ;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * @var array Login guards
     */
    protected $loginGuards = [
        "api" => "api",
        "ldap"   => 'ldap'
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    public function Login(Request $request){
        /** Check if this is not the default password */
        if($request->password == "123456"){
            $sendReset = new ForgotPasswordController ;

            $sendReset->resetSendEmail($request) ;

            return response()->json(
                [
                    "code" => 200 , // redirct to reset password
                    "msg" => "You must reset the default password - Reset Link sent to your email"
                ],
                203
            );
        }

        /** Find user login type  */
        $loginGuard = "api" ; // for test

        if($loginGuard == "api"){
            return $this->NormalLogin($request) ;
        }elseif($loginGuard == "ldap"){
            return $this->LdapLogin($request) ;
        }else{
            return response()->json(['code' => 400 , "msg" => "Undefined login function !"]);
        }
    }


    public function NormalLogin(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(["code" => 401 ,'error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(["code" => 500 ,'error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(
            [
                "code" => 200 ,
                "token" => $token
            ],
            200
        );
    }

    public function LdapLogin(Request $request){

//        $Ldapconfig = [
//           // "auto_connect" => false ,
//            "base_dn" => "DN=Users,DC=wezara,DC=co",
//            "port" => 389 ,
//            "admin_username" => "Administrator",
//            "admin_password" => "@Pass123",
//            "timeout" => 3 ,
//            "domain_controllers" => [
//                '192.168.8.103'
//            ]
//
//        ];
        //$provider = new \Adldap\Connections\Provider($Ldapconfig);
        //Adldap::auth()->attempt("Administrator", "@Pass123");
        //$provider->connect();

        $bindAsUser = true;

        //Adldap::auth()->attempt("CN=Administrator,CN=Users,DC=wezara,DC=co", "@Pass123", $bindAsUser);
        //\Artisan::call('adldap:import');
        //\Artisan::call('adldap:import', array('--yes'=> true));

        //return $username = Adldap::search()->select('userprincipalname')->find("user1");
        return $username = Adldap::search()->find("user1");

        //$users = $users = Adldap::search()->users()->get();
        return response()->json($user) ;
        //return $user = Adldap::search()->users()->find('user1');
    }

    public function formatLdapUser(){}

}



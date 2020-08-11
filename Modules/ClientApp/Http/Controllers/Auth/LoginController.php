<?php

namespace Modules\ClientApp\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Adldap\Laravel\Facades\Adldap;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\DB;


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
        "ldap" => 'ldap'
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


//    public function Login(Request $request){
//
//        /** Find user login type  */
//        $loginGuard = "api" ; // for test
//
//        if($loginGuard == "api"){
//            return $this->NormalLogin($request) ;
//        }elseif($loginGuard == "ldap"){
//            return $this->LdapLogin($request) ;
//        }else{
//            return response()->json(['code' => 400 , "msg" => "Undefined login function !"]);
//        }
//    }


    public function NormalLogin(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');
        $credentials['status'] = 1;

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(["code" => 401, 'error' => 'بيانات تسجيل الدخول غير صحيحة'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(["code" => 500, 'error' => 'could_not_create_token'], 500);
        }

        /* if($this->checkUserDefaultPassword($request , Auth::user()) == true){
             return response()->json(
                 [
                     "code" => 203 , // redirct to reset password
                     "msg" => "You must reset the default password - Reset Link sent to your email"
                 ],
                 203
             );
         }*/

        $translations = \DB::table("trans_table")
            ->select(\DB::raw('*'))
            ->get();
        foreach ($translations as $key => $value) {
            if (isset($value->key_pos)) {
                $keyname = $value->key_name . '@' . $value->key_pos . '@' . $value->key_type;
            } else {
                $keyname = $value->key_name . '@' . $value->key_type;
            }
            $ararray[$keyname] = $value->value_ar;
            $enarray[$keyname] = $value->value_en;
        }
        if ($translations) {
            $trans1 = array("en" => $enarray);
            $trans2 = array("ar" => $ararray);
        }

        $month =  date('n');
        $currentmtp = \DB::select(\DB::raw("select mtp.id, mtp.name, fys.start_date, curdate(), fye.end_date from mtp , fiscal_year fys, fiscal_year fye where
mtp.tenant_id = 1  and fys.id = mtp.mtp_start and fye.id = mtp.mtp_end and
CURDATE() >= fys.start_date and CURDATE() <= fye.end_date"));

        $currentmtpstartdate = $currentmtp[0]->start_date;
        $currentmtpenddate = $currentmtp[0]->end_date;
       $getAllYears = \DB::select(\DB::raw("SELECT * FROM `fiscal_year` WHERE start_date >= '$currentmtpstartdate' and start_date <= '$currentmtpenddate'"));

        $currentYear = 1;
        foreach ($getAllYears as $key => $years) {
            if($years->start_date < date('Y-m-d') && $years->end_date > date('Y-m-d')) {
                $currentYear = $currentYear;
                $currentYear++;
            }
        }

        $currentPeriod = '';
        if(in_array($month, [4,5,6])) {
            $currentPeriod = 1;
        } else if(in_array($month, [7,8,9])) {
            $currentPeriod = 2;
        } else if(in_array($month, [10,11,12])) {
            $currentPeriod = 3;
        } else if(in_array($month, [1,2,3])) {
            $currentPeriod = 4;
        }

        // all good so return the token
        $time=JWTAuth::factory()->getTTL() * 60;
//        echo $time;
//        die();
        $ipaddress = \Request::ip();
        $useragent = \Request::userAgent();
        $user=Auth::user()->id;

        \DB::table('login_activities')->insert(
            ['user_id' => $user, 'user_agent' => $useragent,'ip_address' => $ipaddress]
        );
        return response()->json(
            [
                "code" => 200,
                "token" => $token,
                'translations' => array_merge($trans1, $trans2),
                "currentMtp" => $currentmtp[0]->id,
                "currentYear" => $currentYear,
                "currentPeriod" => $currentPeriod
            ],
            200,
            [
                'Access-Control-Expose-Headers' => 'Authorization',
                'Authorization' => "Bearer " . $token
            ]
        );
    }

    public function LdapLogin(Request $request)
    {

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
        return response()->json($user);
        //return $user = Adldap::search()->users()->find('user1');
    }


//    protected function guard()
//    {
//        return Auth::guard('admin');
//    }

    public function checkUserDefaultPassword(Request $request, $user)
    {
        /** Check if this is not the default password */
        if (\Hash::check($request->password, $user->default_password)) {
            $sendReset = new ForgotPasswordController;

            $sendReset->resetSendEmail($request);

            return true;
        } else {
            false;
        }
    }

    public function formatLdapUser()
    {
    }

    protected function guard()
    {
        return \Auth::guard('tenant_user');
    }

}



<?php

namespace Modules\ClientApp\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use DB ;
use App\User ;
use Hash ;
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords ;


    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function resetPass(Request $request){

        if($this->reset($request) != false){
            $user = User::where('email' , $request->email)->first() ;
            $user->password = bcrypt($request->password) ;
            $user->save();

            return response()->json([
                "code" => 200 ,
                "msg"  => "Password has been updated"
            ]);
        }else{
            return response()->json([
                "code" => 404 ,
                "msg"  => "User not Exist"
            ]);
        }
    }

    public function reset (Request $request){
        $user = DB::table("password_resets")
            ->where('email' , $request->email)->first() ;

        if(Hash::check($request->token, $user->token)){
            return $user ;
        }else{
            return false;
        }
    }
}

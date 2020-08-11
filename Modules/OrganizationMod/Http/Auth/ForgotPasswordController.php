<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\User;
use Illuminate\Http\Request;



class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function resetSendEmail(Request $request)
    {

        if(!$user = User::where('email' , $request->email)->first()){
            return response()->json([
                "code" => 404 ,
                "msg"  => "user Not Exist"
            ]);
        }
        if($this->sendResetLinkEmail($request)){
            return response()->json([
                "code" => 200 ,
                "msg"  => "Email has been sent "
            ]);
        }
    }




}

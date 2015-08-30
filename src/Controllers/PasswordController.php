<?php
/**
 * Created by PhpStorm.
 * User: karthik
 * Date: 8/30/15
 * Time: 4:24 PM
 */
namespace Paverblock\Easyauth\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    /***
     * Send a reset link to the given user
     * @param Request $request
     * @return mixed
     */
    public function sendForgotPasswordEmail(Request $request)
    {
        # validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return jsend()->error()
                ->message('Invalid E-Mail Address')
                ->errors($validator->errors()->all())
                ->get();
        }

        # Check if the user exists and if user is not logged in using a provider
        $user = User::where(array('email' => $request->email))->first();
        if (empty($user)) {
            return jsend()->error()
                ->message('This E-Mail is not available in our records')
                ->get();
        }
        if (!empty($user->provider)) {
            return jsend()->error()
                ->message('E-Mail has been registered using ' . $user->provider)
                ->get();
        }

        # Generate random token
        $token = str_random(50);

        # Insert the token into the database
        $reset = DB::table('password_resets')->where('email', '=', $user->email)->first();
        if (empty($reset)) {
            DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => Carbon::now()
            ]);
        } else {
            DB::table('password_resets')
                ->where('email', '=', $user->email)
                ->update(['token' => $token, 'created_at' => Carbon::now()]);
        }

        # Send user a mail with this token
        Mail::send('easyauth.emails.password', ['user' => $user, 'token' => $token], function ($message) use ($user) {
            $message->from(config('easyauth.email.sender_email'), config('easyauth.email.sender_name'));
            $message->to($user->email);
            $message->subject(config('easyauth.email.forgot_password_subject'));
        });
        return jsend()->success()
                ->message('Mail sent successfully')
                ->get();
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function passwordReset(Request $request)
    {
        # Validate request params
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6|same:password_confirmation',
            'password_confirmation' => 'required|min:6'
        ]);

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        # Check if the reset token exists
        $reset = DB::table('password_resets')
            ->where(array('email' => $credentials['email'], 'token' => $credentials['token']))
            ->where('created_at', '>=', Carbon::now()->subMinutes(60))
            ->first();

        if (empty($reset)) {
            # If there is no token
            return Redirect::back()->withErrors(['Token Invalid or Expired'])->withInput();
        } else {

            # Changes user password
            $user = User::where(array('email' => $credentials['email']))->first();
            $user->password = bcrypt($credentials['password']);
            $user->save();

            # Delete the reset token
            DB::table('password_resets')
                ->where(array('email' => $credentials['email'], 'token' => $credentials['token']))
                ->delete();

            return Redirect::back()->withMessage('Password changed successfully')->withInput();
        }
    }

    /***
     * Show the password reset form
     * @param Request $request
     * @return $this
     */
    public function getPasswordResetForm(Request $request) {
        # validate the request
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return view('easyauth.views.password-reset')->with('message', 'Invalid Link');
        } else {
            return view('easyauth.views.password-reset')->with('token', $request->input('token'));
        }
    }


    /***
     * Change user password if not social login
     * @param Request $request
     * @return string
     */
    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
        ]);
        # Check if the user is not logged in using a provider
        $user = Auth::user();
        if (!empty($user->provider)) {
            return jsend()->error()
                ->message('E-Mail has been registered using ' . $user->provider)
                ->get();
        }
        $user->password(bcrypt($request->input('password')));
        $user->save();
        return jsend()->success()
            ->message('Password changed successfully')
            ->get();
    }
}
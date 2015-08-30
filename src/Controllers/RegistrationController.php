<?php

namespace Paverblock\Easyauth\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Created by PhpStorm.
 * User: karthik
 * Date: 8/30/15
 * Time: 4:19 PM
 */
class RegistrationController extends Controller
{
    /**
     * Register a user
     * @param Request $request
     * @return static
     */
    public function registerUser(Request $request)
    {
        # Validate request params
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'first_name' => 'required|max:25',
            'last_name' => 'required|max:25'
        ]);

        # Return response if request params are invalid
        if ($validator->fails()) {
            return jsend()->error()
                ->message('Invalid Input')
                ->errors($validator->errors()->all())
                ->get();
        }

        # Check if user exists
        $user = User::where(['email' => $request['email']])->first();

        # If user exists
        if (!empty($user)) {
            # Check if user has been registered with a provider
            if (!empty($user->provider)) {
                return jsend()->error()
                    ->message('E-Mail has been registered using ' . $user->provider)
                    ->get();
            }

            # Check if the user is already verified
            if ($user->verified) {
                return jsend()->error()
                    ->message('E-Mail has been already registered.')
                    ->get();
            }

            # Resend activation email to user if user not verified
            else if (!$user->verified) {
                return $this->sendActivationMail($user);
            }
        }

        # If user does not exists
        else {
            # Create the user
            $user = User::create([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
            ]);
            return $this->sendActivationMail($user);
        }
    }

    /***
     * Handles click on activation E-Mail
     * @param Request $request
     * @return mixed
     */
    public function confirmRegistration(Request $request) {
        # Validate request params
        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ]);
        # Return response if request params are invalid
        if ($validator->fails()) {
            return view('easyauth.views.activation')->with('message', 'Token not provided');
        }

        # Get token from request
        $token = $request->input('token');

        # Check if token exists in the database and it has not expired
        $activation = DB::table('email_activations')
            ->where('created_at', '>=', Carbon::now()->subMinutes(60))
            ->where('token', '=', $token)
            ->first();

        if (empty($activation)) {
            return view('easyauth.views.activation')->with('message', 'Token Invalid or Expired');
        }

        # Activate the user if token is valid
        $user = User::where(['email' => $activation->email])->first();
        $user->verified = true;
        $user->save();

        return view('easyauth.views.activation')->with('message', 'Account Activated Successfully');
    }

    /**
     * Sends an activation mail to the user
     * @param $user
     * @return mixed
     * @internal param Request $request
     */
    public function sendActivationMail($user)
    {
        # Generate random token
        $token = str_random(50);

        # Upsert the token into the database
        $activation = DB::table('email_activations')->where(['email' => $user->email])->first();
        if (empty($activation)) {
            DB::table('email_activations')->insert(
                [
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]
            );
        } else {
            DB::table('email_activations')
                ->where('email', '=', $user->email)
                ->update(['token' => $token, 'created_at' => Carbon::now()]);
        }

        # Send user a mail with this token
        Mail::send('easyauth.emails.activation', ['user' => $user, 'token' => $token], function ($message) use ($user) {
            $message->from(config('easyauth.email.sender_email'), config('easyauth.email.sender_name'));
            $message->to($user->email);
            $message->subject(config('easyauth.email.email_activation_subject'));
        });

        return jsend()->success()
            ->message('You have been registered, an activation link has been sent to your E-Mail.')
            ->get();
    }

    /***
     * Deactivate an account
     * @return string
     */
    public function deactivateUser()
    {
        $user = Auth::user();
        $user->delete();
    }
}
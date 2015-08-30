<?php

namespace Paverblock\Easyauth\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Google;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    /***
     * Create a new user or return an API token if the user already exists (For Social Logins)
     * @param $profile
     * @return array
     */
    public function authenticateOrCreateUser($profile)
    {
        # Figure out if a user exists by using the key (id) given by the provider
        $user = User::where(['provider_key' => $profile['provider_key'], 'provider' => $profile['provider']])->first();

        # If the user does not exist, create the user and return an API token
        if (empty($user)) {
            $user = new User;
            $user->first_name = $profile['first_name'];
            $user->last_name = $profile['last_name'];
            $user->email = $profile['email'];
            $user->provider = $profile['provider'];
            $user->provider_key = $profile['provider_key'];
            $user->avatar = $profile['avatar'];
            $user->save();

            $token = JWTAuth::fromUser($user);
            return (array('api-token' => $token, 'user' => $user));
        } # Else generate an API token for the existing user
        else {
            $token = JWTAuth::fromUser($user);
            return (array('api-token' => $token, 'user' => $user));
        }
    }


    /**
     * Autheticate user using email and password
     * @param Request $request
     * @return array
     */
    public function authenticate(Request $request)
    {
        # validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return jsend()->error()
                ->message('Invalid Input')
                ->errors($validator->errors()->all())
                ->get();
        }

        # grab credentials from the request
        $credentials = $request->only('email', 'password');

        # verify that the email does not belong to a provider
        $user = User::where(['email' => $credentials['email']])->first();
        if (!empty($user->provider)) {
            return jsend()->error()
                ->message('E-Mail has been registered using ' . $user->provider)
                ->errors($validator->errors()->all())
                ->get();
        }

        try {
            # attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return jsend()->error()
                    ->message('Invalid Credentials. Login Failed.')
                    ->get();
            }
        } catch (JWTException $e) {
            # Something went wrong whilst attempting to encode the token
            return jsend()->error()
                ->message('Could not create token.')
                ->errors([$e->getMessage()])
                ->get();
        }
        
        $user = Auth::user();

        if ($user->verified) {
            # All good so return the token
            return jsend()->success()
                ->message('User logged in successfully')
                ->data(['user' => $user, 'api-token' => $token])
                ->get();
        } else {
            # User not verified yet
            return jsend()->error()
                ->message('Account Activation Pending. Please check your E-Mail.')
                ->get();
        }
    }

    /**
     * Generates tokens for facebook social login
     * @param Request $request
     * @return static
     */
    public function authenticateFacebook(Request $request)
    {
        # Get access token from request
        #$accessToken = new AccessToken(array('access_token' => $request->input('access_token')));

        $accessToken = new AccessToken(array('access_token' => 'CAAFNoAIv7IMBAA7nXuT2WCoLrHN7Sfi96SizcjlIweZBjQZAA2Rzwj6es97hbZBfON4dIlmarqaIFZAZBMyCOe06wi13i19GlaZCFZCgVjPIk7aYaYa0CC1XVnN3jeAG3oJzur9x9ld2oMLT26VsR6ZBvkuaIaPt0uMQspZBgc6ZAvZCTZCPqsDQEwn4cmvo0IRwwZA5AWIeCENZCpUgZDZD'));

        # Create a new provider which takes values from config file
        $provider = new Facebook([
            'clientId' => config('easyauth.facebook.clientId'),
            'clientSecret' => config('easyauth.facebook.clientSecret'),
            'redirectUri' => config('easyauth.facebook.redirectUri'),
            'graphApiVersion' => config('easyauth.facebook.graphApiVersion')
        ]);

        try {
            # We got an access token, let's now get the owner details
            $ownerDetails = $provider->getResourceOwner($accessToken);

            $profile = array(
                'provider_key' => $ownerDetails->getId(),
                'first_name' => $ownerDetails->getFirstName(),
                'last_name' => $ownerDetails->getLastName(),
                'email' => $ownerDetails->getEmail(),
                'avatar' => $ownerDetails->getPictureUrl(),
                'provider' => 'Facebook'
            );

            # Use these details to create a new profile or return a token in case the user exists
            return $this->authenticateOrCreateUser($profile);

        } catch (Exception $e) {
            # Failed to get user details
            exit('Something went wrong: ' . $e->getMessage());
        }
    }

    /***
     * Generates tokens for Google Social Login
     * @param Request $request
     * @return array
     */
    public function authenticateGoogle(Request $request)
    {

        # Get access token from request
        #$accessToken = new AccessToken(array('access_token' => $request->input('access_token')));

        $accessToken = new AccessToken(array('access_token' => 'ya29.3gFWZcLeCgaKJ-rmDE7znkTtuTA1p-7Fv4PgP4EFSn8gc10pG_jotwIDraqvsq9_jGiO'));

        # Create a new provider which takes values from config file
        $provider = new Google([
            'clientId' => config('easyauth.google.clientId'),
            'clientSecret' => config('easyauth.google.clientSecret'),
            'redirectUri' => config('easyauth.google.redirectUri'),
            'scopes' => config('easyauth.google.scopes')
        ]);

        try {

            # We got an access token, let's now get the owner details
            $ownerDetails = $provider->getResourceOwner($accessToken);

            $profile = array(
                'provider_key' => $ownerDetails->getId(),
                'first_name' => $ownerDetails->getFirstName(),
                'last_name' => $ownerDetails->getLastName(),
                'email' => $ownerDetails->getEmail(),
                'avatar' => $ownerDetails->getAvatar(),
                'provider' => 'Google+'
            );

            # Use these details to create a new profile or return a token in case the user exists
            return $this->authenticateOrCreateUser($profile);

        } catch (Exception $e) {
            # Failed to get user details
            exit('Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Invalidates API token
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        JWTAuth::parseToken()->invalidate();
        return jsend()->success()
                ->message('Successfully logged out')
                ->get();
    }
}

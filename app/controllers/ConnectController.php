<?php
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

/*
|--------------------------------------------------------------------------
| ConnectController: Handles the connection related sites
|--------------------------------------------------------------------------
*/
class ConnectController extends BaseController
{
	/*
    |===================================================
    | <GET> | showConnect: renders the connect page
    |===================================================
    */
    public function showConnect()
    {
        /*
        // getting paypal api context
        $apiContext = PayPalHelper::getApiContext();

        // building up redirect url
        $redirectUrl = OpenIdSession::getAuthorizationUrl(
            route('paypal.buildToken'),
            array('profile', 'email', 'phone'),
            null,
            null,
            null,
            $apiContext
        );
        */
        // selecting logged in user
        $user = Auth::user();

        // prepare stuff for google drive auth        
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENTID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENTSECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECTURL']);
        $client->setScopes(array('https://spreadsheets.google.com/feeds', 'email'));
        $client->setAccessType('offline');                

        // returning view
        return View::make('connect.connect',
            array(
                //'redirect_url' => $redirectUrl,
                //'paypal_connected' => $user->isPayPalConnected(),
                'stripe_connected'      => $user->isStripeConnected(),
                'stripeButtonUrl'       => OAuth2::getAuthorizeURL(),
                'googlespreadsheet_connected'      => $user->isGoogleSpreadsheetConnected(),
                'googleSpreadsheetButtonUrl'       => $client->createAuthUrl(),
            )
        );
    }

    /*
    |===================================================
    | <GET> | connectProvider: return route for connecting a provider
    |===================================================
    */
    public function connectProvider($provider)
    {

    	if ($provider == 'stripe') {
    		$user = Auth::user();
            if(Input::has('code'))
            {
    			// get the token with the code
    			$response = OAuth2::getRefreshToken(Input::get('code'));

    			if(isset($response['refresh_token']))
    			{
	    			$user->stripeRefreshToken = $response['refresh_token'];
                    $user->stripeUserId = $response['stripe_user_id'];

	    			Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    	            $account = Stripe\Account::retrieve($user->stripeUserId);
        	        // success
            	    $returned_object = json_decode(strstr($account, '{'), true);

                    // save user
                    $user->ready = 'connecting';

                    // setting name if is null
                    if (strlen($user->name) == 0) {
                        $user->name = $returned_object['display_name'];
                    }
                    if (strlen($user->zoneinfo) == 0) {
                        $user->zoneinfo = $returned_object['country'];
                    }

                    // saving user
                    $user->save();

                    IntercomHelper::connected($user,'stripe');

                    Queue::push('CalculateFirstTime', array('userID' => $user->id));
            	    
    			} else if (isset($response['error'])) {

    				Log::error($response['error_description']);
    				return Redirect::route('connect.connect')
    					->with('error', 'Something went wrong, try again later');
    			} else {

    				Log::error("Something went wrong with stripe connect, don't know what");
    				return Redirect::route('connect.connect')
    					->with('error', 'Something went wrong, try again later');
    			}

    		} else if (Input::has('error')) {
    			// there was an error in the request

                Log::error(Input::get('error_description'));
    			return Redirect::route('connect.connect')
    				->with('error',Input::get('error_description'));
    		} else {
    			// we don't know what happened
                Log::error('Unknown error with user: '.$user->email);
    			return Redirect::route('connect.connect')
    				->with('error', 'Something went wrong, try again');
    		}
    	}

        if ($provider == 'googlespreadsheet') {

            $user = Auth::user();

            if (Input::has('code')) {
                $client = new Google_Client();
                $client->setClientId($_ENV['GOOGLE_CLIENTID']);
                $client->setClientSecret($_ENV['GOOGLE_CLIENTSECRET']);
                $client->setRedirectUri($_ENV['GOOGLE_REDIRECTURL']);
                $client->setScopes(array('https://spreadsheets.google.com/feeds', 'email'));
                $client->setAccessType('offline');                

                $client->authenticate(Input::get('code'));
                $access_stuff = json_decode($client->getAccessToken(), true);

                Log::info($access_stuff);

                Session::put("gtoken", $access_stuff['access_token']);

                $user->googleSpreadsheetRefreshToken = $access_stuff['refresh_token'];
                $user->save();

/*
                IntercomHelper::connected($user,'googlespreadsheet');                

                // $user->googleSpreadsheetUserId = $access_stuff['stripe_user_id'];
                $user->googleSpreadsheetRefreshToken = $access_stuff;
                $user->ready = 'connecting';
                $user->save();
*/

                $serviceRequest = new DefaultServiceRequest($access_stuff['access_token']);
                ServiceRequestFactory::setInstance($serviceRequest);

                $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
                $spreadsheetFeed = $spreadsheetService->getSpreadsheets();

                // Session::put('spreadsheetFeed', $spreadsheetFeed->asXML());

                return View::make('connect.googleSpreadsheetConnect')->with('spreadsheetFeed', $spreadsheetFeed);
            }

            // return Redirect::route('connect.connect')
            //    ->with('success', ucfirst($provider).' connected.');
        }

  	// return Redirect::route('auth.dashboard')
   	//	->with('success', ucfirst($provider).' connected.');

    }


    /*
    |===================================================
    | <GET> | doDisconnect: disconnects the active user
    |===================================================
    */
    public function doDisconnect($service)
    {
        // NOTE: should we also remove the collected DB data?

        // selecting the logged in User
        $user = Auth::user();

        if ($service == "stripe") {
            // disconnecting stripe

            // removing stripe key
            $user->stripe_key = "";
            $user->stripeUserId = "";
            $user->stripeRefreshToken = "";
            $user->ready = 'notConnected';

        } else if ($service == "braintree") {
            // disconnecting paypal

            // removing paypal refresh token
            $user->paypal_key = "";

        }

        // saving modification on user
        $user->save();

        // redirect to connect
        return Redirect::route('connect.connect')
        	->with('success', 'Disconnected from ' . $service . '.');
    }


    /*
    |===================================================
    | <POST> | doConnect: updates user service data stripe only
    |===================================================
    */
    public function doConnect()
    {
        // Validation
        $rules = array(
            'stripe' => 'min:16|max:64|required'
        );

        // run the validation rules on the inputs
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            // validation error -> sending back
            $failedAttribute = $validator->invalid();
            return Redirect::back()
                ->with('error',$validator->errors()->get(key($failedAttribute))[0]) // send back errors
                ->withInput(); // sending back data
        } else {
            // validator success
            try {

                // trying to login with this key
                Stripe\Stripe::setApiKey(Input::get('stripe'));
                $account = Stripe\Account::retrieve(); // catchable line
                // success
                $returned_object = json_decode(strstr($account, '{'), true);

                // updating the user
                $user = Auth::user();
                $user->ready = 'connecting';

                // setting key
                $user->stripe_key = Input::get('stripe');

                // setting name if is null
                if (strlen($user->name) == 0) {
                    $user->name = $returned_object['display_name'];
                }
                if (strlen($user->zoneinfo) == 0) {
                    $user->zoneinfo = $returned_object['country'];
                }

                // saving user
                $user->save();

                IntercomHelper::connected($user,'stripe');

                Queue::push('CalculateFirstTime', array('userID' => $user->id));

            } catch(Stripe\Error\Authentication $e) {
                // code was invalid
                return Redirect::back()->with('error',"Authentication unsuccessful!");
            }

        // redirect to get stripe
        return Redirect::route('auth.dashboard')
                        ->with(array('success' => 'Stripe connected.'));

        }
    }

    /*
    |===================================================
    | <POST> | doSaveSuggestion: updates user service data stripe only
    |===================================================
    */
    public function doSaveSuggestion()
    {
        $rules = array(
            'suggestion' => 'required'
            );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            // validation error -> sending back
            $failedAttribute = $validator->invalid();
            return Redirect::back()
                ->with('error',$validator->errors()->get(key($failedAttribute))[0]) // send back errors
                ->withInput(); // sending back data
        } else {
            DB::table('suggestions')->insert(array(
                'suggestion' => Input::get('suggestion'),
                'email' => Auth::user()->email));
        }

        return Redirect::route('connect.connect')
                        ->with(array('success' => "Thank you, we'll get in touch"));
    }

    /*
    |===================================================
    | <POST> | showGoogleSpreadsheetConnect: wizard for the Google Spreadsheet connect
    |===================================================
    */
    public function showGoogleSpreadsheetConnect($step)
    {

        // selecting logged in user
        $user = Auth::user();

        // dd(Input::get('spreadsheet');

        $serviceRequest = new DefaultServiceRequest(Session::get('gtoken'));
        ServiceRequestFactory::setInstance($serviceRequest);

        $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
        $spreadsheetFeed = $spreadsheetService->getSpreadsheets();

        if ($step == 2) {
            Session::put("spreadsheetname", Input::get('spreadsheet'));
            $spreadsheet = $spreadsheetFeed->getByTitle(Input::get('spreadsheet'));
            $worksheetFeed = $spreadsheet->getWorksheets();
            return View::make('connect.googleSpreadsheetConnect')->with(
                array(
                    'step' => $step,
                    'worksheetFeed' => $worksheetFeed
                )
            );
        }

        if ($step == 3) {
            Session::put("worksheetname", Input::get('worksheet'));
            $spreadsheet = $spreadsheetFeed->getByTitle(Session::get('spreadsheetname'));
            $worksheetFeed = $spreadsheet->getWorksheets();
            $worksheet = $worksheetFeed->getByTitle(Input::get('worksheet'));
            $listFeed = $worksheet->getListFeed();
            $listArray = array();
            foreach ($listFeed->getEntries() as $entry) {
                $values = $entry->getValues();
                $listArray[] = $values;
            }

            return View::make('connect.googleSpreadsheetConnect')->with(
                array(
                    'step' => $step,
                    'listArray' => $listArray
                )
            );
        }
    }
}
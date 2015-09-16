<?php

use Facebook\Facebook;
use Facebook\PersistentData\PersistentDataInterface;

/**
* --------------------------------------------------------------------------
* FacebookConnector:
*       Wrapper functions for Facebook connection
* Usage:
*       $connector->connect();
* --------------------------------------------------------------------------
*/

class LaravelFacebookSessionPersistendDataHandler implements PersistentDataInterface {
    /**
     * @var string Prefix to use for session variables.
     */
    protected $sessionPrefix = 'FBRLH_';

    /**
     * @inheritdoc
     */
    public function get($key) {
        return Session::get($this->sessionPrefix . $key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value) {
        Session::put($this->sessionPrefix . $key, $value);
    }
}

class FacebookConnector extends GeneralServiceConnector
{
    protected static $service          = 'facebook';
    protected static $permissions      = array('manage_pages', 'read_insights');
    protected static $loginPermissions = array('email', 'public_profile');
    protected static $userInfo = array('email', 'first_name', 'last_name');
    protected static $dataHandler = null;

    protected $fb;

    /* -- Constructor -- */
    function __construct($user) {
        parent::__construct($user);
        $this->fb = self::setFB();
    }

    /**
     * getFB
     * --------------------------------------------------
     * Returns the facebook entity.
     * @return Facebook
     * --------------------------------------------------
     */
    public function getFB() {
        return $this->fb;
    }

    /**
     * setFB
     * --------------------------------------------------
     * Creates and returns the facebook entity.
     * @return Facebook
     * --------------------------------------------------
     */
    public static function setFB() {
        self::$dataHandler = new LaravelFacebookSessionPersistendDataHandler;
        $fb = new Facebook(array(
            'app_id'                  => $_ENV['FACEBOOK_APP_ID'],
            'app_secret'              => $_ENV['FACEBOOK_APP_SECRET'],
            'default_graph_version'   => $_ENV['FACEBOOK_DEFAULT_GRAPH_VERSION'],
            'persistent_data_handler' => self::$dataHandler
        ));
        return $fb;
    }

    /**
     * getFacebookConnectUrl
     * Returns the facebook connect url, based on config.
     * --------------------------------------------------
     * @return string
     * --------------------------------------------------
     */
    public function getFacebookConnectUrl() {
        $helper = $this->fb->getRedirectLoginHelper();
        if (App::environment('local')) {
            // we must use this in development
            return $helper->getLoginUrl('http://localhost:8001/service/facebook/connect', static::$permissions);
        }

        return $helper->getLoginUrl(route('service.facebook.connect'), static::$permissions);
    }

    /**
     * getFacebookLoginUrl
     * Returns the facebook connect url for login, based on config.
     * --------------------------------------------------
     * @return string
     * --------------------------------------------------
     */
    public static function getFacebookLoginUrl() {
        $fb = self::setFB();
        $helper = $fb->getRedirectLoginHelper();
        if (App::environment('local')) {
            // we must use this in development
            return $helper->getLoginUrl('http://localhost:8001/service/facebook/login', static::$loginPermissions);
        }
        return $helper->getLoginUrl(route('service.facebook.login'), static::$loginPermissions);
    }

    /**
     * connect
     * --------------------------------------------------
     * @return FacebookSession
     * --------------------------------------------------
     */
    public function connect() {
        return $this->getConnection()->access_token;
    }

    /**
     * ================================================== *
     *                   PUBLIC SECTION                   *
     * ================================================== *
     */

    /**
     * saveTokens
     * Retrieving the access tokens, OAUTH.
     * --------------------------------------------------
     * @return None
     * --------------------------------------------------
     */
    public function saveTokens(array $parameters=array()) {
        $helper = $this->fb->getRedirectLoginHelper();

        if (App::environment('local')) {
            $this->createConnection($helper->getAccessToken(str_replace(8000, 8001, URL::full())), '');
        } else {
            $this->createConnection($helper->getAccessToken(), '');
        }
    }

    /**
     * loginWithFacebook
     * Logs a user in with facebook.
     * --------------------------------------------------
     * @return string (route)
     * --------------------------------------------------
     */
    public static function loginWithFacebook() {
        $fb = self::setFB();
        $helper = $fb->getRedirectLoginHelper();

        /* Retrieving access token */
        if (App::environment('local')) {
            $accessToken = $helper->getAccessToken(str_replace(8000, 8001, URL::full()));
        } else {
            $accessToken = $helper->getAccessToken();
        }

        /* Retrieving user info. */
        $response = $fb->get('/me?fields=' . implode(',', self::$userInfo), $accessToken);
        $userInfo = $response->getGraphUser();

        /* Saving user/logging in registered. */
        $registeredUser = User::where('email', $userInfo['email'])->first();
        if (is_null($registeredUser)) {
            $user = User::create(array(
                'email'  => $userInfo['email'],
                'name'   => $userInfo['first_name'] . ' ' . $userInfo['last_name'],
            ));
            $user->createDefaultProfile();
            Auth::login($user);
            return 'signup-wizard.financial-connections';
        } else {
            Auth::login($registeredUser);
            return 'dashboard.dashboard';
        }
    }


    /**
     * disconnect
     * --------------------------------------------------
     * disconnecting the user from facebook.
     * @throws ServiceNotConnected
     * --------------------------------------------------
     */
    public function disconnect() {
        parent::disconnect();
        /* deleting all plans. */
        FacebookPage::where('user_id', $this->user->id)->delete();
    }

    /**
     * populateData
     * --------------------------------------------------
     * Collecting the initial data from the service.
     * --------------------------------------------------
     */
    public function populateData() {
        Queue::push('FacebookPopulateData', array(
            'user_id' => $this->user->id
        ));
    }

} /* FacebookConnector */

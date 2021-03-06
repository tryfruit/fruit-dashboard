<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

/* This filter runs before a request is served */
App::before(function($request) {
    /* Make secure connection */
    if ( ( ! App::environment('local')) and (!Request::secure()) ) {
        return Redirect::secure(Request::path());
    }
});

/* This filter runs after a request is served */
App::after(function($request, $response) {
    /* Update last activity for the User */
    if (Auth::check()) {
        $settings = Auth::user()->settings;
        $settings->last_activity = Carbon::now();
        $settings->save();
    }
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
    /* Not authenticated, ajax request */
    if (Auth::guest() && Request::ajax()) {
        return Response::make('Unauthorized', 401);
    }

    /* Not authenticated */
    if (Auth::guest()) {
        return Redirect::route('auth.signin');
    }
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
    if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
    if (Session::token() !== Input::get('_token'))
    {
        throw new Illuminate\Session\TokenMismatchException;
    }
});

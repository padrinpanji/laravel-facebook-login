<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
	$data = array();

    if (Auth::check()) {
        $data = Auth::user();
    }
	
    return View::make('user', array('data'=>$data));
});

Route::get('logout', function() {
    Auth::logout();
    return Redirect::to('/');
});

Route::get('login/fb', function() {
    $facebook = new Facebook(Config::get('facebook'));
    $params = array(
        'redirect_uri' => url('/login/fb/callback'),
        'scope' => 'public_profile, email, user_hometown, user_location',
    );
    return Redirect::to($facebook->getLoginUrl($params));
});

Route::get('login/fb/callback', function() {
    $code = Input::get('code');
    if (strlen($code) == 0) return Redirect::to('/')->with('message', 'There was an error communicating with Facebook');

    $facebook = new Facebook(Config::get('facebook'));
    $uid = $facebook->getUser();

    if ($uid == 0) return Redirect::to('/')->with('message', 'There was an error');
	
    $me = $facebook->api('/me','get', array("fields"=>"name,email, first_name, last_name, location"));
	
	/***** Start Facebook FQL ****/
	// $fql = "SELECT uid,name,first_name,middle_name,last_name,pic_big,hometown_location,current_location,email,website,sex,birthday_date,current_address  FROM user WHERE uid=me()";
	// $info = $facebook->api( "/fql?q=" . urlencode($fql));
	echo '<pre>'; print_r($me); echo '</pre>'; exit;
	/***** End Facebook FQL ****/
	
	
    $profile = Profile::whereUid($uid)->first();
    if (empty($profile)) {

        $user = new User;
        $user->name = $me['first_name'].' '.$me['last_name'];
        $user->email = $me['email'];
        $user->photo = 'https://graph.facebook.com/'.$me['username'].'/picture?type=large';

        $user->save();

        $profile = new Profile();
        $profile->uid = $uid;
        $profile->username = $me['username'];
        $profile = $user->profiles()->save($profile);
    }

    $profile->access_token = $facebook->getAccessToken();
    $profile->save();

    $user = $profile->user;

    Auth::login($user);

    return Redirect::to('/')->with('message', 'Logged in with Facebook');
});

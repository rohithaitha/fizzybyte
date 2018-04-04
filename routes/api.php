<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register','Api\Auth\UsersApiController@register');

Route::post('login','Api\Auth\UsersApiController@login');

Route::post('requestotp','Api\Auth\UsersApiController@requestotp');

Route::post('checkUser','Api\Auth\UsersApiController@checkUser');

Route::post('passwordReset','Api\Auth\UsersApiController@passwordReset');

Route::post('refresh','Api\Auth\UsersApiController@refresh');

Route::post('sendmail','mailController@sendmail');

Route::post('payUSuccess','Api\Payment\UsersPaymentController@payUSuccess');
Route::post('payUFailure','Api\Payment\UsersPaymentController@payUFailure');



	


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:api'],function(){

	Route::post('InitSalesTransaction','Api\Payment\UsersPaymentController@InitSalesTransaction');
	Route::post('completeSalesTransaction','Api\Payment\UsersPaymentController@completeSalesTransaction');
    Route::post('checkTxnStatus','Api\Payment\UsersPaymentController@checkTxnStatus');
    Route::post('closeSalesTransaction','Api\Payment\UsersPaymentController@closeSalesTransaction');
    

	Route::post('GetUsertransactions','Api\Payment\UsersPaymentController@GetUsertransactions');	

	Route::post('getHash1','Api\Payment\UsersPaymentController@getHash1');

	Route::post('syncCurrentVendingItems','Api\ItemsMenu\ItemMenuController@syncCurrentVendingItems');
	Route::post('viewTodaysMenu','Api\ItemsMenu\ItemMenuController@viewTodaysMenu');	
	Route::post('viewRatings','Api\ItemsMenu\ItemMenuController@viewRatings');
	Route::post('submitRating','Api\ItemsMenu\ItemMenuController@submitRating');
	
	Route::post('logout','Api\Auth\UsersApiController@logout');
	

});


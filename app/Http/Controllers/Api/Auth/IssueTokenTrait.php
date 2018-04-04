<?php 
namespace App\Http\Controllers\Api\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;

trait IssueTokenTrait{
	public function issueToken(Request $request, $grantType, $scope = ""){

        $client = Client::where('password_client', 1)->first();

		$params = [
    		'grant_type' => $grantType,
    		'client_id' => $client->id,
    		'client_secret' => $client->secret,    	
            'username'      => $request->input('email'),	
            'password'      => $request->input('password'),
    		'scope' => $scope
    	];  
        
    	$request->request->add($params);
    	$proxy = Request::create('oauth/token', 'POST');
    	return Route::dispatch($proxy);
	}
}
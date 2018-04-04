<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;

class UsersApiController extends Controller
{
    use IssueTokenTrait;

function checkUser($mobile_no)
{    

    if($mobile_no)
    {
        $validMobile = DB::table('users')
                     ->where([['mobile_no', '=',$mobile_no ]
                 ])                                
                ->count();
        if($validMobile > 0){

            return true;

        }

        return false;

    }

  return false;

}

function register(Request $request)
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
     
    $date = \Carbon\Carbon::today()->subMinutes(30); 

    $valid = validator($request->only('email', 'name', 'password','mobile_no','otp'), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'mobile_no' => 'required|unique:users',
        'otp' => 'required'
    ]);

    if ($valid->fails()) {
        //$jsonError=response()->json('status' => 'failed','errors' => $valid->errors()->all(), 400);
        return response()->json(['status' => 'failed','errors' => $valid->errors()->all(),400]);
    }

    $data = request()->only('email','name','password','mobile_no','otp');


    $validOTP = DB::table('mobile_otp_codes')
                     ->where([['mobile_num', '=',$data['mobile_no'] ],                     
                     ['created_at', '>=', $date],
                     ['otp', '=', $data['otp']]
                 ])                                
                ->count();

    if($validOTP > 0) {   
    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => bcrypt($data['password']),
        'mobile_no' => $data['mobile_no']
    ]);    

    return $this->issueToken($request, 'password');
   }

  return response()->json(['status'=>'failed','errors'=>'invalid OTP/details'] , 200);       

}

function passwordReset(Request $request)
{

    $date = \Carbon\Carbon::today()->subMinutes(30); 

    $valid = validator($request->only('mobile_no','otp','password'), [                    
        'mobile_no' => 'required',
        'otp' => 'required', 
        'password'=> 'required'       
    ]);

    $alreadyaUser = $this->checkUser($request->input('mobile_no'));

    if(!$alreadyaUser){

        return response()->json(['status' => 'failed','message' => 'Mobile number not registered, please register',400]);

    }

    if ($valid->fails()) {
        
        return response()->json(['status' => 'failed','message' => $valid->errors()->all(),400]);
    }

    $data = request()->only('password','mobile_no','otp');


    $validOTP = DB::table('mobile_otp_codes')
                     ->where([['mobile_num', '=',$data['mobile_no'] ],                     
                     ['created_at', '>=', $date],
                     ['otp', '=', $data['otp']]
                 ])                                
                ->count();

    if($validOTP > 0) {   
    

    try{

    DB::table('users')
            ->where('mobile_no', $data['mobile_no'])
            ->update(['password' => bcrypt($data['password'])]);

    return response()->json(['status' => 'success','message' => 'Password updated successfully, try login now',200]);

    }catch(Exception $e){
        logger('Exception in password reset '.$e);
    }
 }

 return response()->json(['status' => 'failed','message' => 'invalid OTP/details provided ',200]);

}


function login(Request $request)
{
    $this->validate($request, [
            'mobile_no' => 'required',
            'password' => 'required'
        ]);

    $mailid_count = DB::table('users')
                ->select( 'email')
                ->where([['mobile_no', '=', $request->input('mobile_no')]
                     ])                
                ->count(); 

    if(!$mailid_count){

     return response()->json(['status' => 'failed','errors' => "Mobile number not registered,Please register first",400]);   

    }

   $mailid = DB::table('users')
                ->select( 'email')
                ->where([['mobile_no', '=', $request->input('mobile_no')]
                     ])                
                ->first(); 
   
    if($mailid){
        
        $request->merge(array('email'=>$mailid->email));
    }




/*    $path = $request->image->storeAs('images', 'rohit.png');

    logger('rohit image file path'.serialize($path));*/

    return $this->issueToken($request, 'password');
}


public function refresh(Request $request){

        $this->validate($request, [
            'refresh_token' => 'required'
        ]);
      return $this->issueToken($request, 'refresh_token');
 }
 
public function logout(Request $request){

        $accessToken = Auth::user()->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);
        $accessToken->revoke();
      return response()->json([], 204);
    }

public function requestotp(Request $request){

    $valid = validator($request->only('mobile_no'), [                        
        'mobile_no' => 'required',
    ]);

    if ($valid->fails()) {        
        return response()->json(['status' => 'failed','errors' => $valid->errors()->all(),400]);
    }

    $mobile_no = $request->input('mobile_no');    
    $otp_token = random_int(100000, 999999);
    try{
    DB::insert('insert into mobile_otp_codes (mobile_num, otp) values (?, ?)', array($mobile_no, $otp_token));
    $inserted = 'yes';
    }catch(Exception $e){
        logger('Exception while inserting otp'.$e);
       
    }
    $username = "aithaxxx@gmail.com";
    $hash = "677725f016df48f10035206858de781ccad27e7f71e4a3725da20e8b47057020";

    // Config variables. Consult http://api.textlocal.in/docs for more info.
    $test = "0";

    // Data for text message. This is the text message data.
    $sender = "FUDBOX"; // This is who the message appears to be from.
    //$numbers = "91996643544"; // A single number or a comma-seperated list of numbers
    $message = "OTP for your Fooodbox registration/password reset is ".$otp_token;
    // 612 chars or less
    // A single number or a comma-seperated list of numbers
    $message = urlencode($message);
    $mobile_no = "91".$mobile_no;
    $data = "username=".$username."&hash=".$hash."&message=".$message."&sender=".$sender."&numbers=".$mobile_no."&test=".$test;
    if($inserted == 'yes'){
    $ch = curl_init('http://api.textlocal.in/send/?');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch); // This is the result from the API
    logger($result);
    curl_close($ch);

     // Convert JSON string to Object
     $respStatus = json_decode($result);    
     if($respStatus->status == 'success'){

      return response()->json(['status'=>'success'] , 200);    

     }

     return response()->json([$result], 200);     

    }    

    return response()->json(['status'=>'failed'], 200);     
    
    
    }

}

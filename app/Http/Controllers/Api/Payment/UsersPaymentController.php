<?php

namespace App\Http\Controllers\Api\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Laravel\Passport\Client;
use Illuminate\Support\Carbon;
use Auth;
use App\Receipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Grammars\Grammar;
use App\Transaction;

class UsersPaymentController extends Controller
{

	function  getHash(Request $request){

    if(Auth::user()->email !== 'admin@fooodbox.in'){

            return response()->json(['status' => 'failed','message' => 'you do not have privillages for this action','count' =>'0' ]);  
                   
        }

		  //$key = "gtKFFx";
      //  $SALT = "eCwWELxi";
        $key = DB::table('setups')
                ->select( 'value')
                ->where([['environment', '=', 'production'],
                     ['is_Active', 1],
                     ['name', '>=', 'payu_key']])                
                ->first();
        $SALT = DB::table('setups')
                ->select( 'value')
                ->where([['environment', '=', 'production'],
                     ['is_Active', 1],
                     ['name', '>=', 'payu_salt']])                
                ->first();

        logger('in GetHash ', ['key' => $key]);
        logger('in GetHash ', ['salt' => $SALT]);
        logger('Get hash reuest',$request->all());        
        logger($request);        

        $txnid = $request->input('txnid');
        $amount = $request->input('amount');
        $firstname = $request->input('firstname');
        $email = $request->input('email');
        $productinfo = $request->input('productinfo');
        $service_provider = $request->input('service_provider');
        $user_credentials = $request->input('user_credentials');
	  
		  $valid = validator($request->only('txnid', 'amount', 'firstname','email','productinfo'), [
	        'txnid' => 'required',
	        'amount'=> 'required',
	        'email' => 'required',
	        'firstname' => 'required',
	        'productinfo' => 'required']);

	    if ($valid->fails()){

	        return response()->json(['status' => 'Error','hash'=>'','Message'=> ' - One of the manadatory field is null']);
	    }

	     $hashSequence = $key.'|'.$txnid.'|'.$amount.'|'.$productinfo.'|'.$firstname.'|'.$email.'|||||||||||'.$SALT;
	        $hash = strtolower(hash('sha512', $hashSequence));
	        
	        $hashSequence = $key.'|'.'vas_for_mobile_sdk'.'|'.'default'.'|'.$SALT;
	        $vas_for_mobile_sdk_hash = strtolower(hash('sha512', $hashSequence));
	        
	        $hashSequence = $key.'|'.'payment_related_details_for_mobile_sdk'.'|'.$user_credentials.'|'.$SALT;
	        $PAYMENT_HASH = strtolower(hash('sha512', $hashSequence));
	        
	        return response()->json(['payment_hash'=>$hash,'vas_for_mobile_sdk_hash'=>$vas_for_mobile_sdk_hash,'payment_related_details_for_mobile_sdk_hash'=>$PAYMENT_HASH ,'status' => 'success','Message'=> ' succesfully generated']);

	}

  function checkNull($value) {
                  if ($value == null) {
                        return '';
                  } else {
                        return $value;
                  }
  }

  function  getHash1(Request $request){

        $key=$request->input('key');
        $salt='c6Q5MXXGxc';      

        $txnId = $request->input('txnid');
        $amount = $request->input('amount');
        $firstName = $request->input('firstname');
        $email = $request->input('email');
        $productName = $request->input('productinfo');
        $udf1=$request->input('udf1');
        $udf2=$request->input('udf2');
        $udf3=$request->input('udf3');
        $udf4=$request->input('udf4');
        $udf5=$request->input('udf5');       
        
    
      $valid = validator($request->only('txnid', 'amount', 'email'), [
          'txnid' => 'required',
          'amount'=> 'required',
          'email' => 'required']);

      if ($valid->fails()){

          return response()->json(['status' => 'Error','hash'=>'','Message'=> ' - One of the manadatory field is null']);
      }

      

    $payhash_str = $key . '|' . $this->checkNull($txnId) . '|' .$this->checkNull($amount)  . '|' .$this->checkNull($productName)  . '|' . $this->checkNull($firstName) . '|' . $this->checkNull($email) . '|' . $this->checkNull($udf1) . '|' . $this->checkNull($udf2) . '|' . $this->checkNull($udf3) . '|' . $this->checkNull($udf4) . '|' . $this->checkNull($udf5) . '||||||' . $salt;     


        $hash = strtolower(hash('sha512', $payhash_str));        
        logger($payhash_str);        
          
      return response()->json(['payment_hash'=>$hash,'status' => 'success','Message'=> ' succesfully generated','Hash_string'=>$payhash_str]);

  }

  public function payUSuccess(Request $request)
    {
        try{
        $txnid = $request->input('txnid');
        $status = $request->input('status'); 
        $transaction_fee = $request->input('transaction_fee');
        $amount = $request->input('amount');
        
        $newpayment = new Receipt;
        
        $newpayment->txnid = $txnid;
        $newpayment->status =  $status;
        $newpayment->request = $request;
        $newpayment->transaction_fee = 'transaction_fee';//$request->input('transaction_fee');
        $newpayment->discount = $request->input('discount');
        $newpayment->amount = $request->input('amount');
        $newpayment->udf1 = empty($request->input('udf1'))?'udf1':$request->input('udf1');
        $newpayment->email = $request->input('email');
        $newpayment->firstname = $request->input('firstname');
        $newpayment->save();
        
        $count = Receipt::where('txnid', $txnid)->count();
        
        return response()->json(['status' => '0','message' => 'inserted succesfully','count' =>$count ]);
        
        }
        catch(Exception $e) {
            
            return response()->json(['status' => '1','message' => 'Exception occured while reading the payment gateway details, your money will be credited back to you in 7 working days','count' =>'0' ]);
            
        }
        
        
        
    }

      public function payUFailure(Request $request)
    {
        try{
        $txnid = $request->input('txnid');
        $status = $request->input('status'); 
        $transaction_fee = $request->input('transaction_fee');
        $amount = $request->input('amount');
        
        $count = Receipt::where('txnid', $txnid)->count();
        
        return response()->json(['status' => '0','message' => 'inserted succesfully','count' =>$count ]);
        
        }
        catch(Exception $e) {
            
            return response()->json(['status' => '1','message' => 'Exception occured while reading the payment gatewat details, your money will be credited back to you in 7 working days','count' =>'0' ]);
            
        }
        
        
        
    }



   public function getUserWalletBalance($p_mobile_no)
    {

        $mobile_no = $p_mobile_no;
        
        $totalReceipts = DB::table('receipts')
                ->where('udf1', $mobile_no)
                ->sum('amount');
                
			    /*    $totalSpedings = DB::table('userspendings')
			                ->where('email', $email_id)
			                ->sum('txnamount');*/
                
                
                $totalSpedings = DB::table('transactions')->where([
									    ['mobile_no', '=', $mobile_no],
									    ['txnstatus', '=', 'success'],
									])->sum('txnamount');
            
            return  ($totalReceipts - $totalSpedings);
                
    }
    
    public function GetUsertransactions(Request $request)
    {
        
        $email_id = Auth::user()->email;    

        $mobile_no = Auth::user()->mobile_no;    

        $userbalance = $this->getUserWalletBalance($mobile_no);
        
        $totalReceipts = DB::table('receipts')
                ->select( 'txnid','amount','created_at')
                ->where('udf1', $mobile_no);
                
                
                
        $totalSpedings = DB::table('transactions')
                ->  select('order as txnid',DB::raw('(txnamount*-1) as amount'),'created_at')
                ->  where([['mobile_no', '=', $mobile_no],
                     ['txnstatus', '=', 'success'],])
                ->union($totalReceipts)
                ->orderBy('created_at', 'desc')
                ->get();
        
        
      
                
            $data = ['email' => $email_id ,'currentbalance' => $userbalance,'spendings' => $totalSpedings];
                
            
            return  response()->json($data);
                
    }
    
    public function InitSalesTransaction(Request $request)
    {
        if(Auth::user()->email !== 'admin@fooodbox.in'){

            return response()->json(['status' => 'failed','message' => 'you do not have privillages for this action','count' =>'0' ]);  
                   
        }
        
        try{        
        
	        $newSale = new Transaction;
	        $newSale->order = $request->input('order');
	        $newSale->email =  $request->input('email'); 
	        $newSale->machine_id = $request->input('machine_id'); 
	        $newSale->item_name = $request->input('item_name');
	        $newSale->itemrack = $request->input('itemrack');
	        $newSale->txnamount = $request->input('txnamount');
	        $newSale->item_image_path = $request->input('item_image_path');
	        $newSale->txnstatus = $request->input('txnstatus');	        	        
	        $newSale->save();
	        
	        
	        $count = Transaction::where('order', $request->input('order'))->count();
	        if($count > 0)
	        {
	          return response()->json(['status' => 'success','message' => 'inserted succesfully','count' =>$count,'order'=> $request->input('order')]);
	        }
	        else{
	            return response()->json(['status' => 'failure','message' => 'problem in inserting the record','count' =>$count ]);
	        }
        
        }
        catch(Exception $e) {
            
            return response()->json(['status' => 'failure','message' => 'Exception occured while initiating the transaction','count' =>'0' ]);
            
        }
        
    }
    
    public function completeSalesTransaction(Request $request)
    {

        
     try{
        
        $count = Transaction::where('order', $request->input('txnid'))->count();
        if($count > 0)
        {
            $userbalance = $this->getUserWalletBalance(Auth::user()->mobile_no);
            
            if($userbalance >= $request->input('txnamount') ){           

            $transactions = Transaction::where('order', $request->input('txnid'))->get();

             $timeElapsed = \Carbon\Carbon::now()->diffInSeconds($transactions[0]->created_at);

             logger('TIme difference'.$timeElapsed);

             logger('TIme now'.\Carbon\Carbon::now());

             if($timeElapsed < 115){ // rejecting payment reuest if it is in last 6 seconds 

            Transaction::where('order', $request->input('txnid'))
                         ->update(['txnstatus' => 'payment received'],['email' => Auth::user()->email],
                          ['mobile_no' => Auth::user()->mobile_no],['request' => $request]);
             
             return response()->json(['status' => 'success','message' => 'payment received','order'=>$request->input('txnid')]);

             }
             return response()->json(['status' => 'invalid_order','message' => 'Time expired','count' =>'0' ]);    
            
                
            }
             else{
              return response()->json(['status' => 'insufficient_funds','message' => 'Insufficient funds, please rechardge your wallet','total_balance'=> $userbalance,'order'=>$request->input('txnid')]);
             }
          
        }
        else{
            return response()->json(['status' => 'invalid_order','message' => 'invalid QR code','count' =>$count,'order'=>$request->input('txnid') ]);
        }    
        }
        catch(Exception $e) {
            
            return response()->json(['status' => 'failed','message' => 'Exception occured while reading the payment gatewat details, your money will be credited back to you in 7 working days','count' =>'0' ]);
            
        }
        
        
    }

    public function closeSalesTransaction(Request $request)
    {

      if(Auth::user()->email !== 'admin@fooodbox.in'){

            return response()->json(['status' => 'failed','message' => 'you do not have privillages for this action','count' =>'0' ]);  
                   
        }

        
     try{
        
        $count = Transaction::where('order', $request->input('txnid'))->count();
        if($count > 0)
        {
            $userbalance = $this->getUserWalletBalance(Auth::user()->email);
            
            if($userbalance >= $request->input('txnamount') ){
            
            Transaction::where('order', $request->input('txnid'))
                         ->update(['txnstatus' => 'success'],['request' => $request]);
             
             return response()->json(['status' => 'success','message' => 'payment received','order'=>$request->input('txnid')]);
                
            }
             else{
              return response()->json(['status' => 'insufficient_funds','message' => 'Insufficient funds, please rechardge your wallet','total_balance'=> $userbalance,'order'=>$request->input('txnid')]);
             }
          
        }
        else{
            return response()->json(['status' => 'invalid_order','message' => 'invalid QR code','count' =>$count,'order'=>$request->input('txnid') ]);
        }    
        }
        catch(Exception $e) {
            
            return response()->json(['status' => 'failed','message' => 'Exception occured while reading the payment gatewat details, your money will be credited back to you in 7 working days','count' =>'0' ]);
            
        }
        
        
    }
    
    public function checkTxnStatus(Request $request)
    {

      if(Auth::user()->email !== 'admin@fooodbox.in'){

            return response()->json(['status' => 'failed','message' => 'you do not have privillages for this action','count' =>'0' ]);  
                   
        }

        
     try{
         
        $ordercount = Transaction::where([['order', '=',$request->input('order') ],
                     ['txnstatus', '=', 'payment received'],])->count();
        if($ordercount > 0)
        {
         
             return response()->json(['status' => 'success','message' => 'payment received']);
                
         }
         else{
              return response()->json(['status' => 'failed','message' => 'Payment not recevied']);
         }
        }
        catch(Exception $e) {
            
            return response()->json(['status' => 'failed','message' => 'Exception occured while reading the payment status, your money will be credited back to you in 7 working days','count' =>'0' ]);
            
        }
        
        
    }


}

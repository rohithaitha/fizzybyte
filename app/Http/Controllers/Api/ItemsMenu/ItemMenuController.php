<?php

namespace App\Http\Controllers\Api\ItemsMenu;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use App\User;
use Laravel\Passport\Client;
use Carbon;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Facades\Storage;


class ItemMenuController extends Controller
{

	function syncCurrentVendingItems(Request $request)
	{

		if(Auth::user()->email !== 'admin@fooodbox.in'){

            return response()->json(['status' => 'failed','message' => 'you do not have privillages for this action','count' =>'0' ]);  
                   
        }


		$url = '/images/default.jpeg';

		//disk initialization 
     try{
	   $disk = Storage::disk('gcs');
	   $currdate = Carbon\Carbon::now();          
       $currdate->format('d-m-Y');
       // create a file
       $path =$disk->put('images/'.$currdate->format('d-m-Y'), $request->image);
       $url = $disk->url($path);
      }catch(Exception $e) {

      	logger('Exception in ItemMenuController wile generating image path');
      	logger($e);
            
       }


		$batch_id = $request->input('batch_id');

		$machine_id = $request->input('machine_id');

		$rec_id = $request->input('id');

		$image_src = $request->input('image_src');

		$item_name = $request->input('item_name');

		$itemdescription = $request->input('description');

		$itemPrice = $request->input('item_price');

		$itemRack = $request->input('item_rack');

		$quantity = $request->input('item_qty');		

		$itemid = $request->input('itemid'); // as of now we ar not getting this field		

		$freshTimeInHours = $request->input('freshTimeInHours'); // as of now, not implemented

		$valid = validator($request->only('item_name', 'item_price', 'machine_id','item_qty','batch_id'), [
			        'item_name' => 'required',
			        'item_price' => 'required',
			        'machine_id' => 'required',
			        'item_qty' => 'required',
			        'batch_id'=>'required'
			    ]);

		try{
			if(!($valid->fails()))
			{

				DB::table('vend_items_now')->where([['machine_id', '=', $machine_id],
								['batch_id', '<>', $batch_id]])->delete();


				$recordId = DB::table('vend_items_now')->insertGetId(
					['machine_id' => $machine_id,
					 'batch_id' => $batch_id ,
					 'itemid' => $itemid,
					 'item_name' => $item_name,
					 'itemPrice'=> $itemPrice,
					 'itemRack' => $itemRack,
					 'quantity' => $quantity,
					 'item_image_path' => $url,
					 'freshTimeInHours'=>$freshTimeInHours,
					 'description'=>$itemdescription,
					 'errormsg'=>''
					]
				);


				if($recordId > 0){
					return response()->json(['status' => 'success','message' => 'Ratings submitted','item_image_path'=>$url]);
				}else{
					return response()->json(['status' => 'failed','message' => 'Failed to update the ratings. Please submit again after some time']);
				}
				
			}else{
/*				$jsonError=response()->json($valid->errors()->all(), 400);
	        	return \Response::json($jsonError);*/
				return response()->json(['status' => 'failed','message' => $valid->errors()->all(),400]);
			}
		} catch(Exception $e) {
            
            return response()->json(['status' => 'failed','message' => 'Exception occured while syncing the records' ]);
            
        }
		
		
	}

	function viewTodaysMenu(Request $request)
	{
		
		$machine_id = $request->input('machine_id');

		try{			
				$menuList = DB::table('vend_items_now')->get();

     			return response()->json(['status' => 'success','data' => $menuList ,'message' => '']);
     			

		} catch(Exception $e) {

            logger('in viewTodaysMenu exception');            
			logger($e);            
            return response()->json(['status' => 'failed','message' => 'Exception occured while viewing todays menu. Please try again after some time' ]);
            
        }
	}

		function viewRatings(Request $request)
	{
		

		$email_id = Auth::user()->email;

		$mobile_no = Auth::user()->mobile_no;
		
		$date = \Carbon\Carbon::today()->subDays(365);

		try{
			if(!empty($email_id))
			{
				$totalPayments = DB::table('transactions')
                     ->where([['mobile_no', '=', $mobile_no],                     
                     ['created_at', '>=', $date]])
                ->orderBy('created_at', 'desc')
				->limit(20)
                ->get();

     			$data = ['email' => $email_id ,'transactions' => $totalPayments];
     			if($data){
     				return  response()->json($data);
     			}else{
     				return response()->json(['status' => 'failed','message' => 'Failed to retrieve the required data. Please try again after some time' ]);
     			}
     			
			}else{
				return response()->json(['status' => 'failed','message' => 'Could not receive required data ']);
			}
		} catch(Exception $e) {
            
            return response()->json(['status' => 'failed','message' => 'Exception occured while viewing recently bought items. Please try again after some time' ]);
            
        }
		
		
	}

	function submitRating(Request $request)
	{
		$email_id = Auth::user()->email;		

		$mobile_no = Auth::user()->mobile_no;		

		$stars = $request->input('stars');

		$comments = $request->input('comments');

		$order = $request->input('order');		

		$valid = validator($request->only( 'stars','order'), [
			        'stars' => 'required',
			        'order' => 'required'			        
			    ]);		

	  
		try{
			if(!($valid->fails()))
			{				
				$ratingUpdate = DB::table('transactions')
							->where([['order', '=', $order],
								['mobile_no', '=', $mobile_no],
								['txnstatus', '=', 'success']])
							->update(['stars' => $stars,'is_Active' => 0,'comments'=>$comments]);
			
				if($ratingUpdate){
					return response()->json(['status' => 'success','message' => 'Ratings submitted']);
				}else{
					return response()->json(['status' => 'failed','message' => 'Failed to update the ratings. Please submit again after some time']);
				}
				
			}else{
				$jsonError=response()->json($valid->errors()->all(), 400);
	        	return \Response::json($jsonError);
				//return response()->json(['status' => 'invalid','message' => 'Ratings are not received']);
			}
		} catch(Exception $e) {
            
            return response()->json(['status' => 'failed','message' => 'Exception occured while updating the ratings. Please submit again after some time' ]);
            
        }
			

		//return  'Ratings submitted';
	}

	function putImage(Request $request)
	{
		
	   //$path = $request->image->storeAs('images', 'rohith.png');


	   $disk = Storage::disk('gcs');

	   $currdate = Carbon\Carbon::now();

          
       $currdate->format('d-m-Y');

       // create a file
       $path =$disk->put('images/'.$currdate->format('d-m-Y'), $request->image);

       logger($path);

       $url = $disk->url($path);

       logger('rohit image file path'.serialize($url));
		
     	return response()->json(['status' => 'success','data' => $url ,'message' => '']);
     			

		
	}

}

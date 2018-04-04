<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use google\appengine\api\mail\Message;

class mailController extends Controller
{
    //

    public function sendmail(Request $reuest){

    	try {
			    $message = new Message();
			    $message->setSender('chinna.rohith@xyz.com');
			    $message->addTo('rohith.aitha@gmail.com');
			    $message->setSubject('Example email');
			    $message->setTextBody('Hello, world!');			    
			    $message->send();
			    logger('Mail Sent');
			} catch (InvalidArgumentException $e) {
			    logger('There was an error in sendmail');
			}

    }
}

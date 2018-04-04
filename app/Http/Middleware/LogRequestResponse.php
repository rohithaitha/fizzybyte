<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use App\RequestResponselog;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        Log::info('app.requests', ['request' => $request->all(), 'response' => $response]);

/*    $Responselog = RequestResponselog::create([
        'email'   => $request->email,
        'request' => serialize( $request->all()),
        'response'=> $response                
        ]);*/
    }
}

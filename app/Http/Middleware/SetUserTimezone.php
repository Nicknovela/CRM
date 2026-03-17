<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $timezone = auth()->user()->timezone ?? config('app.timezone', 'America/La_Paz');
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
            Carbon::setTestNow(Carbon::now($timezone));
        }

        return $next($request);
    }
}

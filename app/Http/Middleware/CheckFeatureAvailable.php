<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFeatureAvailable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $feature
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $feature)
    {
        if (auth()->check() && auth()->user()->tenant) {
            $tenant = auth()->user()->tenant;

            if (!$tenant->hasFeature($feature)) {
                return redirect()->route('billing.features')
                    ->with('error', "The feature '{$feature}' is not available in your current plan.");
            }
        }

        return $next($request);
    }
}

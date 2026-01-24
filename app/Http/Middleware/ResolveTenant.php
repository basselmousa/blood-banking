<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $tenant = auth()->user()->tenant;

            if ($tenant) {
                // Set tenant in the request
                $request->setTenant($tenant);

                // Set tenant ID in session
                session(['tenant_id' => $tenant->id]);

                // Check if subscription is active
                if (!$tenant->isActive()) {
                    return redirect()->route('billing.subscribe')
                        ->with('warning', 'Your subscription has expired. Please renew to continue.');
                }
            }
        }

        return $next($request);
    }
}

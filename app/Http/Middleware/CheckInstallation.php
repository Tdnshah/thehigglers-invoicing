<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        // Check if a company exists
        $companyExists = Company::exists();
        $userExists = User::exists();

        // If no company or no user (super admin), redirect to installation
        if (!$companyExists || !$userExists) {
            if (!$request->routeIs('install.*')) {
                return redirect()->route('install.index');
            }
        } else {
             // If already installed, prevent access to installation routes
            if ($request->routeIs('install.*')) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}

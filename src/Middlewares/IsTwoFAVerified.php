<?php

namespace Solutionforest\FilamentEmail2fa\Middlewares;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Solutionforest\FilamentEmail2fa\Interfaces\RequireTwoFALogin;
use Solutionforest\FilamentEmail2fa\Pages\TwoFactorAuth;

class IsTwoFAVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = Filament::auth()->user();

        if ($user instanceof MustVerifyEmail && Filament::hasEmailVerification() && !$user->hasVerifiedEmail()) {
            return $next($request);
        }

        try {
            $routeName = $request->route()->getName();
        } catch (\Exception $e) {
            $routeName = null;
        }

        if ($user == null || $routeName == TwoFactorAuth::getRouteName() || $routeName == Filament::getCurrentPanel()->generateRouteName('auth.logout')) {
            return $next($request);
        }

        if ($user instanceof RequireTwoFALogin && $user->isTwoFaVerified($request->session()->getId())) {

            return $next($request);
        }

        return redirect(route(TwoFactorAuth::getRouteName()));
    }
}

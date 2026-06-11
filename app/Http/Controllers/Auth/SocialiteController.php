<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\SocialProvider;
use App\Exceptions\SocialAuthException;
use App\Http\Controllers\Controller;
use App\Services\Auth\SocialiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SocialiteController extends Controller
{
    public function __construct(private readonly SocialiteService $socialite) {}

    public function redirect(Request $request, string $provider): Response
    {
        $resolved = $this->resolveProvider($provider);

        // Only the guest login/signup flow consumes this; the authenticated
        // linking flow never reads it, so don't leave it dangling in the session.
        if (! $request->user() && $request->filled('invitation')) {
            $request->session()->put('socialite.invitation', $request->string('invitation')->toString());
        }

        return Socialite::driver($resolved->value)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $resolved = $this->resolveProvider($provider);

        try {
            $oauthUser = Socialite::driver($resolved->value)->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->with('error', "Unable to sign in with {$resolved->label()}. Please try again.");
        }

        if ($authUser = $request->user()) {
            try {
                $this->socialite->linkToUser($authUser, $resolved, $oauthUser);
            } catch (SocialAuthException $e) {
                return redirect()->route('connections.edit')->with('error', $e->getMessage());
            }

            return redirect()->route('connections.edit')
                ->with('success', "{$resolved->label()} connected.");
        }

        try {
            $this->socialite->loginOrRegister(
                $resolved,
                $oauthUser,
                $request->session()->pull('socialite.invitation'),
            );
        } catch (SocialAuthException $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        }

        return redirect()->intended(route('dashboard'));
    }

    private function resolveProvider(string $provider): SocialProvider
    {
        return SocialProvider::fromEnabled($provider) ?? abort(404);
    }
}

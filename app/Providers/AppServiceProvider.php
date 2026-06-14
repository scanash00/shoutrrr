<?php

namespace App\Providers;

use App\Listeners\BindWorkspaceToAccessToken;
use App\Listeners\SetCurrentWorkspaceOnLogin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Passport;
use Override;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        RateLimiter::for('mcp', fn ($request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        Gate::before(function (User $user, string $ability): ?bool {
            if (! str_starts_with($ability, 'workspace.')) {
                return null;
            }

            return $user->hasAllPermissions([$ability], Context::get('workspace_id'));
        });

        Event::listen(Login::class, SetCurrentWorkspaceOnLogin::class);
        Event::listen(AccessTokenCreated::class, BindWorkspaceToAccessToken::class);

        Passport::authorizationView(
            /** @param array<string, mixed> $parameters */
            function (array $parameters): Response {
                $user = request()->user();

                return response()->view('oauth.authorize', array_merge($parameters, [
                    'workspaces' => $user
                        ? $user->workspaceMemberships()->with('workspace')->get()
                            ->pluck('workspace')->filter()->values()
                        : collect(),
                ]));
            }
        );

    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

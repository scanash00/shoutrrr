<?php

declare(strict_types=1);

use App\Mcp\Servers\ShoutrrrServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Mcp\Facades\Mcp;

// Throttle the OAuth authorize/token endpoints so consent and token-exchange
// can't be hammered (credential/consent abuse).
Route::middleware('throttle:20,1')->group(function (): void {
    Mcp::oauthRoutes();
});

Mcp::web('/mcp', ShoutrrrServer::class)
    ->middleware(['auth:api', 'throttle:mcp']);

// Browsers that GET /mcp would otherwise hit the package's bare "405 Method Not
// Allowed" handler, which renders as a confusing error page. Override the GET
// route (registered last, so it wins the lookup) to serve a friendly landing
// page to humans while preserving the spec's 405/Allow: POST for protocol
// clients that aren't a browser.
Route::get('/mcp', function (Request $request) {
    if ($request->acceptsHtml()) {
        return Inertia::render('mcp/landing');
    }

    return response('', 405)->header('Allow', 'POST');
})->name('mcp.landing');

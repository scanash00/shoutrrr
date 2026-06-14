<?php

declare(strict_types=1);

use App\Mcp\Servers\ShoutrrrServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp', ShoutrrrServer::class)
    ->middleware(['auth:api', 'throttle:mcp']);

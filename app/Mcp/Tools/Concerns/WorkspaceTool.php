<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Concerns;

use App\Models\McpGrantWorkspace;
use App\Models\User;
use Illuminate\Support\Facades\Context;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Passport\AccessToken;

/**
 * Base for all workspace-scoped MCP tools. Resolves the workspace bound to the
 * caller's OAuth access token and installs the same `workspace_id` Context that
 * WorkspaceMiddleware sets for web requests, so model global-scopes and policies
 * behave identically. Also overrides the in-memory current_workspace_id so service
 * code that reads it operates on the bound workspace, not the user's last web choice.
 */
abstract class WorkspaceTool extends Tool
{
    /**
     * Resolve the workspace id bound to the current access token, or null.
     */
    protected function workspaceId(Request $request): ?string
    {
        $accessToken = $request->user()?->currentAccessToken();

        if (! $accessToken instanceof AccessToken) {
            return null;
        }

        $tokenId = $accessToken->oauth_access_token_id;

        return McpGrantWorkspace::where('access_token_id', $tokenId)->value('workspace_id');
    }

    /**
     * Install workspace scope for this tool call. Returns the workspace id, or null
     * if no binding exists (the tool should error).
     */
    protected function bindWorkspace(Request $request): ?string
    {
        $workspaceId = $this->workspaceId($request);

        if ($workspaceId === null) {
            return null;
        }

        Context::add('workspace_id', $workspaceId);

        /** @var User|null $user */
        $user = $request->user();
        if ($user !== null) {
            $user->current_workspace_id = $workspaceId; // in-memory only, not saved
        }

        return $workspaceId;
    }

    /**
     * Guard an irreversible action behind an explicit confirm flag. Returns an error
     * Response to return immediately when unconfirmed, or null to proceed.
     */
    protected function requireConfirmation(Request $request, string $consequence): ?Response
    {
        if ($request->get('confirm') === true) {
            return null;
        }

        return Response::error(
            $consequence.' This action cannot be undone. Confirm with the human, then call again with confirm set to true.'
        );
    }
}

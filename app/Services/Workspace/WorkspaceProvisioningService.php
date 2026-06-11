<?php

declare(strict_types=1);

namespace App\Services\Workspace;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Support\Str;

class WorkspaceProvisioningService
{
    public function __construct(private readonly WorkspaceInvitationService $invitations) {}

    /**
     * Provision workspace access for a freshly created user: accept a pending
     * invitation when a token is present, then guarantee the user lands with at
     * least one workspace. If the invitation was invalid or expired (acceptByToken
     * fails silently by design), they still get a default workspace rather than
     * being stranded with none.
     */
    public function provisionForNewUser(User $user, ?string $invitationToken): void
    {
        if ($invitationToken) {
            $this->invitations->acceptByToken($invitationToken, $user);
        }

        if (! $user->workspaceMemberships()->exists()) {
            $this->createDefaultWorkspace($user);
        }
    }

    public function createDefaultWorkspace(User $user): void
    {
        $workspace = Workspace::create([
            'name' => "{$user->name}'s Workspace",
            'slug' => $this->uniqueSlug($user->name),
            'owner_id' => $user->id,
        ]);

        WorkspaceMembership::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => WorkspaceRole::Owner,
        ]);

        $user->forceFill(['current_workspace_id' => $workspace->id])->save();
    }

    private function uniqueSlug(string $name): string
    {
        do {
            $slug = Str::slug($name.' workspace').'-'.Str::lower(Str::random(5));
        } while (Workspace::where('slug', $slug)->exists());

        return $slug;
    }
}

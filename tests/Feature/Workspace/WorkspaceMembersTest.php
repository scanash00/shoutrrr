<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMembership;
use Illuminate\Support\Facades\Notification;

/**
 * @return array{0: Workspace, 1: User}
 */
function ownerInWorkspace(): array
{
    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->owner()->create(['workspace_id' => $workspace->id, 'user_id' => $owner->id]);

    return [$workspace, $owner];
}

test('owner can invite a member', function () {
    Notification::fake();
    [$workspace, $owner] = ownerInWorkspace();

    $this->actingAs($owner)->post(route('settings.workspace.invite'), [
        'email' => 'new@example.com',
        'role' => 'member',
    ])->assertRedirect();

    $this->assertDatabaseHas('workspace_invitations', [
        'workspace_id' => $workspace->id,
        'email' => 'new@example.com',
    ]);
});

test('member cannot invite', function () {
    $workspace = Workspace::factory()->create();
    $member = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->create(['workspace_id' => $workspace->id, 'user_id' => $member->id]);

    $this->actingAs($member)->post(route('settings.workspace.invite'), [
        'email' => 'new@example.com', 'role' => 'member',
    ])->assertForbidden();
});

test('owner cannot change their own role', function () {
    [$workspace, $owner] = ownerInWorkspace();
    $membership = $owner->getMembershipForWorkspace($workspace->id);

    $this->actingAs($owner)->patch(route('settings.workspace.members.update', $membership), ['role' => 'admin'])
        ->assertSessionHasErrors();
});

test('owner can remove a member', function () {
    [$workspace, $owner] = ownerInWorkspace();
    $member = User::factory()->create();
    $membership = WorkspaceMembership::factory()->create(['workspace_id' => $workspace->id, 'user_id' => $member->id]);

    $this->actingAs($owner)->delete(route('settings.workspace.members.remove', $membership))->assertRedirect();

    $this->assertDatabaseMissing('workspace_memberships', ['id' => $membership->id]);
});

test('owner can cancel a pending invitation', function () {
    [$workspace, $owner] = ownerInWorkspace();
    $invitation = WorkspaceInvitation::factory()->create(['workspace_id' => $workspace->id]);

    $this->actingAs($owner)->delete(route('settings.workspace.invitations.cancel', $invitation))->assertRedirect();

    $this->assertDatabaseMissing('workspace_invitations', ['id' => $invitation->id]);
});

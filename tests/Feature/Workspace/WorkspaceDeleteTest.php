<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;

test('owner can delete workspace and memberships cascade', function () {
    $workspace = Workspace::factory()->create();
    $owner = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->owner()->create(['workspace_id' => $workspace->id, 'user_id' => $owner->id]);

    $this->actingAs($owner)->delete(route('workspaces.destroy', $workspace))->assertRedirect();

    $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
    $this->assertDatabaseMissing('workspace_memberships', ['workspace_id' => $workspace->id]);
    $this->assertNull($owner->fresh()->current_workspace_id);
});

test('non owner cannot delete workspace', function () {
    $workspace = Workspace::factory()->create();
    $member = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->create(['workspace_id' => $workspace->id, 'user_id' => $member->id]);

    $this->actingAs($member)->delete(route('workspaces.destroy', $workspace))->assertForbidden();

    $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);
});

test('deleting current workspace reassigns to another membership', function () {
    $a = Workspace::factory()->create();
    $b = Workspace::factory()->create();
    $owner = User::factory()->create(['current_workspace_id' => $a->id]);
    WorkspaceMembership::factory()->owner()->create(['workspace_id' => $a->id, 'user_id' => $owner->id]);
    WorkspaceMembership::factory()->create(['workspace_id' => $b->id, 'user_id' => $owner->id]);

    $this->actingAs($owner)->delete(route('workspaces.destroy', $a))->assertRedirect();

    $this->assertSame($b->id, $owner->fresh()->current_workspace_id);
});

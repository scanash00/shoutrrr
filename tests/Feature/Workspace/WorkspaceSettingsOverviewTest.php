<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;

test('owner can view and update workspace', function () {
    $this->withoutVite();

    $workspace = Workspace::factory()->create(['name' => 'Old']);
    $owner = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->owner()->create(['workspace_id' => $workspace->id, 'user_id' => $owner->id]);

    $this->actingAs($owner)->get(route('settings.workspace'))->assertOk();

    $this->actingAs($owner)->patch(route('settings.workspace.update'), ['name' => 'New'])
        ->assertRedirect();

    $this->assertSame('New', $workspace->fresh()->name);
});

test('member cannot update workspace', function () {
    $workspace = Workspace::factory()->create(['name' => 'Old']);
    $member = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->create(['workspace_id' => $workspace->id, 'user_id' => $member->id]);

    $this->actingAs($member)->patch(route('settings.workspace.update'), ['name' => 'New'])
        ->assertForbidden();

    $this->assertSame('Old', $workspace->fresh()->name);
});

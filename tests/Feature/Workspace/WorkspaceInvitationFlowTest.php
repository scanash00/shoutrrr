<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;

test('logged in invitee accepts via link', function () {
    $workspace = Workspace::factory()->create();
    [$plain, $hash] = WorkspaceInvitation::generateToken();
    WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'guest@example.com',
        'token' => $hash,
    ]);
    $user = User::factory()->create(['email' => 'guest@example.com']);

    $this->actingAs($user)->get(route('workspace.invitation', $plain))->assertRedirect(route('dashboard'));

    $this->assertTrue($user->fresh()->isMemberOfWorkspace($workspace->id));
});

test('guest invitee sees acceptance page', function () {
    $this->withoutVite();

    $workspace = Workspace::factory()->create();
    [$plain, $hash] = WorkspaceInvitation::generateToken();
    WorkspaceInvitation::factory()->create(['workspace_id' => $workspace->id, 'token' => $hash]);

    $this->get(route('workspace.invitation', $plain))->assertOk();
});

test('invalid token redirects home with error', function () {
    $this->get(route('workspace.invitation', 'nope'))
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors();
});

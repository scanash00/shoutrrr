<?php

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;

test('registration creates a default owned workspace', function () {
    $this->post(route('register'), [
        'name' => 'Dana',
        'email' => 'dana@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'dana@example.com')->firstOrFail();

    $this->assertNotNull($user->current_workspace_id);
    $workspace = Workspace::find($user->current_workspace_id);
    $this->assertTrue($user->is($workspace->owner));
    $this->assertSame(WorkspaceRole::Owner, $user->getMembershipForWorkspace($workspace->id)->role);
});

test('registration with invitation joins instead and verifies email', function () {
    $workspace = Workspace::factory()->create();
    [$plain, $hash] = WorkspaceInvitation::generateToken();
    WorkspaceInvitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'invitee@example.com',
        'role' => WorkspaceRole::Admin->value,
        'token' => $hash,
    ]);

    $this->post(route('register').'?invitation='.$plain, [
        'name' => 'Invitee',
        'email' => 'invitee@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'invitee@example.com')->firstOrFail();

    $this->assertTrue($user->isMemberOfWorkspace($workspace->id));
    $this->assertSame(1, Workspace::count()); // no personal workspace created
    $this->assertNotNull($user->email_verified_at);
});

<?php

use App\Concerns\HasWorkspaceScope;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

#[Table(name: 'workspace_memberships')]
class ScopedFixture extends Model
{
    use HasUuids, HasWorkspaceScope;

    protected $guarded = [];

    public $timestamps = true;
}

test('scope filters and autofills workspace id', function () {
    $a = Workspace::factory()->create();
    $b = Workspace::factory()->create();

    Context::add('workspace_id', $a->id);

    // workspace_id is auto-filled from context on create
    $row = ScopedFixture::create(['user_id' => $a->owner_id, 'role' => 'member']);
    $this->assertSame($a->id, $row->workspace_id);

    // a row in workspace B is invisible while context is workspace A
    ScopedFixture::withoutGlobalScope('workspace')->create([
        'workspace_id' => $b->id, 'user_id' => $b->owner_id, 'role' => 'member',
    ]);

    $this->assertSame(1, ScopedFixture::count());
});

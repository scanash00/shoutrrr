<?php

it('seeds the default user before starting development services', function (): void {
    $composer = json_decode(file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($composer['scripts']['dev'])->toContain('@php artisan db:seed --class=DefaultUserSeeder --force --no-interaction');
});

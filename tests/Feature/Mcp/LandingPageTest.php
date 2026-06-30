<?php

test('browsers visiting the mcp endpoint get a friendly landing page', function (): void {
    $this->get('/mcp', ['Accept' => 'text/html'])
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('mcp/landing'));
});

test('non-browser GET requests still receive the spec 405 with Allow: POST', function (): void {
    $response = $this->get('/mcp', ['Accept' => 'application/json']);

    $response->assertStatus(405);
    $response->assertHeader('Allow', 'POST');
});

test('the mcp protocol endpoint still requires authentication on POST', function (): void {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [],
    ], ['Accept' => 'application/json, text/event-stream']);

    $response->assertUnauthorized();
});

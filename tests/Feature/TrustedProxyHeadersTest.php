<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// The raw Location header is asserted on purpose: assertRedirect('/login')
// resolves the expected path against the current request, so it would also
// accept a host taken from a spoofed header.

it('redirects a normal request to its own host', function () {
    $this->get('http://rrhh.example.test/registro')
        ->assertStatus(302)
        ->assertHeader('Location', 'http://rrhh.example.test/login');
});

it('generates https urls when the proxy forwards the https scheme', function () {
    $this->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('http://rrhh.example.test/registro')
        ->assertStatus(302)
        ->assertHeader('Location', 'https://rrhh.example.test/login');
});

it('ignores forwarded headers that could alter generated urls', function (array $headers) {
    $this->withHeaders(['X-Forwarded-Proto' => 'https', ...$headers])
        ->get('http://rrhh.example.test/registro')
        ->assertStatus(302)
        ->assertHeader('Location', 'https://rrhh.example.test/login');
})->with([
    'host' => [['X-Forwarded-Host' => 'evil.example']],
    'port' => [['X-Forwarded-Port' => '8443']],
    'prefix' => [['X-Forwarded-Prefix' => '/evil']],
]);

it('ignores a spoofed forwarded host on plain http', function () {
    $this->withHeaders(['X-Forwarded-Host' => 'evil.example'])
        ->get('http://rrhh.example.test/registro')
        ->assertStatus(302)
        ->assertHeader('Location', 'http://rrhh.example.test/login');
});

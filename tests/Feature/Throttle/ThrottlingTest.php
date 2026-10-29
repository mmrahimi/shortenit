<?php

use App\Models\User;

it('rejects requests immediately after the configured limit', function ($limiter, $method, $uri, $expectedStatus, $asUser = false) {
    $user = $asUser ? User::factory()->create() : null;

    $status = function () use ($method, $uri, $user) {
        auth()->forgetGuards();

        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];

        if ($user) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$user->createToken('test')->plainTextToken;
        }

        return test()->call($method, $uri, [], [], [], $server, '[]')->status();
    };

    foreach (range(1, limitOf($limiter)) as $ignored) {
        expect($status())->toBe($expectedStatus);
    }

    expect($status())->toBe(429);
})->with([
    'login' => ['auth', 'POST', '/api/login', 422],
    'register' => ['auth', 'POST', '/api/register', 422],
    'urls' => ['create-url', 'POST', '/api/urls', 422],
    'redirect' => ['redirect', 'GET', '/api/unknown', 404],
    'logout' => ['general', 'POST', '/api/logout', 204, true],
    'links' => ['general', 'GET', '/api/urls', 200],
]);

it('gives each user their own link creation bucket', function () {
    [$first, $second] = User::factory()->count(2)->create();

    foreach (range(1, limitOf('create-url')) as $i) {
        shorten("https://example.com/$i", user: $first)->assertCreated();
    }

    shorten(URL_A, user: $first)->assertStatus(429);
    shorten(URL_A, user: $second)->assertCreated();
});

<?php

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Str;

it('returns a short url that matches the redirect route', function () {
    $response = shorten(URL_A, (string) Str::uuid())->assertCreated();

    expect($response->json('short_url'))
        ->toBe(route('redirect', $response->json('short_code')));
});

it('stores a guest link with the right owner and hash', function () {
    $token = (string) Str::uuid();

    shorten(URL_A, $token)->assertCreated();

    expect(ShortUrl::first())
        ->user_id->toBeNull()
        ->guest_token->toBe($token)
        ->original_url->toBe(URL_A)
        ->url_hash->toBe(hash('sha256', URL_A));
});

it('stores a user link with user_id and no guest token', function () {
    $user = User::factory()->create();

    shorten(URL_A, user: $user)->assertCreated();

    expect(ShortUrl::first())
        ->user_id->toBe($user->id)
        ->guest_token->toBeNull();
});

it('returns the same link when the same guest shortens the same url twice', function () {
    $token = (string) Str::uuid();

    $first = shorten(URL_A, $token)->json('short_url');
    $second = shorten(URL_A, $token)->json('short_url');

    expect($second)->toBe($first)
        ->and(ShortUrl::count())->toBe(1);
});

it('returns the same link when the same user shortens the same url twice', function () {
    $user = User::factory()->create();

    $first = shorten(URL_A, user: $user)->json('short_url');
    $second = shorten(URL_A, user: $user)->json('short_url');

    expect($second)->toBe($first)
        ->and(ShortUrl::count())->toBe(1);
});

it('creates separate links for different urls from the same guest', function () {
    $token = (string) Str::uuid();

    shorten(URL_A, $token);
    shorten(URL_B, $token);

    expect(ShortUrl::where('guest_token', $token)->count())->toBe(2);
});

it('creates separate links when different guests shorten the same url', function () {
    $first = shorten(URL_A, (string) Str::uuid())->json('short_url');
    $second = shorten(URL_A, (string) Str::uuid())->json('short_url');

    expect($first)->not->toBe($second)
        ->and(ShortUrl::count())->toBe(2);
});

it('ignores a stale guest cookie when a valid user token is sent', function () {
    $user = User::factory()->create();

    shorten(URL_A, (string) Str::uuid(), $user)->assertCreated();

    expect(ShortUrl::first())
        ->user_id->toBe($user->id)
        ->guest_token->toBeNull();
});

it('requires original url', function () {
    apiPost('/urls', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('original_url');
});

it('rejects invalid urls', function () {
    foreach (['not a url', 'ftp://test.com'] as $url) {
        shorten($url)->assertUnprocessable();
    }

    expect(ShortUrl::count())->toBe(0);
});

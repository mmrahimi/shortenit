<?php

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Str;

it('returns a short url that matches the redirect route', function () {
    $response = shorten(URL_A, (string) Str::uuid())->assertCreated();

    expect($response->json('short_url'))
        ->toBe(route('redirect', $response->json('short_code')));
});

it('issues a uuid guest cookie to a new guest and stores it on the row', function () {
    $cookie = shorten(URL_A)->assertCreated()->getCookie('guest_token', false);

    expect($cookie)->not->toBeNull()
        ->and(Str::isUuid($cookie->getValue()))->toBeTrue()
        ->and(ShortUrl::first()->guest_token)->toBe($cookie->getValue());
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

it('does not issue a guest cookie to an authenticated user', function () {
    $response = shorten(URL_A, user: User::factory()->create())->assertCreated();

    expect($response->getCookie('guest_token', false))->toBeNull();
});

it('moves a guest link to the user on login without touching other guests', function () {
    $user = createDoe();
    $mine = (string) Str::uuid();
    $other = (string) Str::uuid();

    shorten(URL_A, $mine);
    shorten(URL_B, $other);

    loginAs($user, $mine)->assertOk();

    expect(ShortUrl::where('user_id', $user->id)->count())->toBe(1)
        ->and(ShortUrl::where('guest_token', $other)->whereNull('user_id')->count())->toBe(1);
});

it('keeps both links when a claimed guest url matches one the user already owns', function () {
    $user = createDoe();
    $token = (string) Str::uuid();

    shorten(URL_A, user: $user);
    shorten(URL_A, $token);

    loginAs($user, $token)->assertOk();

    expect(ShortUrl::where('user_id', $user->id)->count())->toBe(2);
});

it('requires original_url', function () {
    apiPost('/urls', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('original_url');
});

it('rejects invalid URLs', function () {
    foreach (['not a url', 'ftp://test.com'] as $url) {
        shorten($url)->assertUnprocessable();
    }

    expect(ShortUrl::count())->toBe(0);
});

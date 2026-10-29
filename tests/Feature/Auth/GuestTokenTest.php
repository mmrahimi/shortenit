<?php

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Str;

it('issues a uuid guest cookie to a new guest and stores its token on the row', function () {
    $cookie = shorten(URL_A)->assertCreated()->getCookie('guest_token', false);

    expect($cookie)->not->toBeNull()
        ->and(Str::isUuid($cookie->getValue()))->toBeTrue()
        ->and(ShortUrl::first()->guest_token)->toBe($cookie->getValue());
});

it("keeps a returning guest's cookie instead of issuing a new one", function () {
    $token = (string) Str::uuid();

    $response = shorten(URL_A, $token)->assertCreated();

    expect($response->getCookie('guest_token', false))->toBeNull()
        ->and(ShortUrl::first()->guest_token)->toBe($token);
});

it('replaces an invalid guest cookie with a fresh uuid', function () {
    $response = apiPost('/urls', ['original_url' => URL_A], ['guest_token' => 'not-a-uuid'])->assertCreated();

    $new = $response->getCookie('guest_token', false)->getValue();

    expect(Str::isUuid($new))->toBeTrue()
        ->and(ShortUrl::first()->guest_token)->toBe($new);
});

it('issues a guest cookie that JavaScript cannot read and that outlives the session', function () {
    $cookie = shorten(URL_A)->getCookie('guest_token', false);

    expect($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getExpiresTime())->toBeGreaterThan(now()->timestamp);
});

it('does not issue a guest cookie to an authenticated user', function () {
    $response = shorten(URL_A, user: User::factory()->create())->assertCreated();

    expect($response->getCookie('guest_token', false))->toBeNull();
});

it('does not issue a guest cookie on redirects', function () {
    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');

    $response = test()->get(route('redirect', $code))->assertRedirect(URL_A);

    expect($response->getCookie('guest_token', false))->toBeNull();
});

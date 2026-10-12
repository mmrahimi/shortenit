<?php

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Str;

it('redirects the returned short code to the original url', function () {
    $shortCode = shorten(URL_A, (string) Str::uuid())->json('short_code');

    test()->get("/api/$shortCode")->assertRedirect(URL_A);
});

it('returns 404 for an unknown code', function () {
    test()->get('/api/urls/unknown')->assertNotFound();
});

it('redirects guest and user links without any auth', function () {
    $guest = shorten(URL_A, (string) Str::uuid())->json('short_code');
    $owned = shorten(URL_B, user: User::factory()->create())->json('short_code');

    test()->get("/api/$guest")->assertRedirect(URL_A);
    test()->get("/api/$owned")->assertRedirect(URL_B);
});

it('treats short codes as case sensitive', function () {
    foreach (['aBcDeF' => URL_A, 'abcdef' => URL_B] as $code => $url) {
        ShortUrl::create([
            'short_code' => $code,
            'original_url' => $url,
            'url_hash' => hash('sha256', $url),
        ]);
    }

    test()->get('/api/aBcDeF')->assertRedirect(URL_A);
    test()->get('/api/abcdef')->assertRedirect(URL_B);
});

it('keeps a guest link working after the guest logs in and claims it', function () {
    $user = createDoe();
    $token = (string) Str::uuid();
    $shortCode = shorten(URL_A, $token)->json('short_code');

    loginAs($user, $token)->assertOk();

    test()->get("/api/$shortCode")->assertRedirect(URL_A);
});

it('does not issue a guest cookie on redirects', function () {
    $shortCode = shorten(URL_A, (string) Str::uuid())->json('short_code');

    $response = test()->get("/api/$shortCode");

    expect($response->getCookie('guest_token', false))->toBeNull();
});

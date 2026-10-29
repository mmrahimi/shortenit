<?php

use App\Models\User;
use Illuminate\Support\Str;

it('redirects the short code to the original url', function () {
    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');

    test()->get(route('redirect', $code))->assertRedirect(URL_A);
});

it('returns 404 for an unknown code', function () {
    test()->get(route('redirect', 'unknown'))->assertNotFound();
});

it('redirects guest and user links without any auth', function () {
    $guest = shorten(URL_A, (string) Str::uuid())->json('short_code');
    $owned = shorten(URL_B, user: User::factory()->create())->json('short_code');

    test()->get(route('redirect', $guest))->assertRedirect(URL_A);
    test()->get(route('redirect', $owned))->assertRedirect(URL_B);
});

<?php

use App\Models\ShortUrl;
use App\Models\User;

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

it('keeps both links when a claimed guest link points to a url the user already shortened', function () {
    $user = createDoe();
    $token = (string) Str::uuid();

    shorten(URL_A, user: $user);
    shorten(URL_A, $token);

    loginAs($user, $token)->assertOk();

    expect(ShortUrl::where('user_id', $user->id)->count())->toBe(2);
});

it('hands the guest links to the new user on registration', function () {
    $token = (string) Str::uuid();
    shorten(URL_A, $token);

    apiPost('/register', NEW_USER, ['guest_token' => $token])->assertCreated();

    expect(ShortUrl::first())
        ->user_id->toBe(User::firstWhere('username', NEW_USER['username'])->id)
        ->guest_token->toBeNull();
});

it('does not hand ownerless links to a logged-in user who logs in again', function () {
    $user = createDoe();
    ShortUrl::create(['short_code' => 'orphan1', 'original_url' => URL_A, 'url_hash' => hash('sha256', URL_A)]);

    apiPost('/login', ['username' => $user->username, 'password' => PASSWORD], [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$user->createToken('test')->plainTextToken,
    ])->assertOk();

    expect(ShortUrl::first()->user_id)->toBeNull();
});

<?php

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

it('registers a user, stores a hashed password and returns a working token', function () {
    $response = apiPost('/register', NEW_USER)->assertCreated();

    $user = User::firstWhere('username', NEW_USER['username']);

    expect($user)->not->toBeNull()
        ->and($user->password)->not->toBe(PASSWORD)
        ->and(Hash::check(PASSWORD, $user->password))->toBeTrue();

    logoutWith($response->json('token'))->assertNoContent();
});

it('lets a registered user log in with the same password', function () {
    apiPost('/register', NEW_USER)->assertCreated();

    apiPost('/login', ['username' => NEW_USER['username'], 'password' => PASSWORD])->assertOk();
});

it('hands the guest links to the new user on registration', function () {
    $token = (string) Str::uuid();
    shorten(URL_A, $token);

    apiPost('/register', NEW_USER, ['guest_token' => $token])->assertCreated();

    expect(ShortUrl::first())
        ->user_id->toBe(User::firstWhere('username', NEW_USER['username'])->id)
        ->guest_token->toBeNull();
});

it('rejects a username that is already taken', function () {
    apiPost('/register', ['username' => createDoe()->username] + NEW_USER)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');

    expect(User::count())->toBe(1);
});

it('requires a username and a password', function ($field) {
    apiPost('/register', array_diff_key(NEW_USER, [$field => null]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with(['username', 'password']);

it('returns a token that authenticates the user', function () {
    $token = loginAs(createDoe(), null)->assertOk()->json('token');

    logoutWith($token)->assertNoContent();
});

it('issues a token that stops working once it expires', function () {
    $user = createDoe();
    $token = loginAs($user, null)->json('token');

    test()->travelTo($user->tokens()->first()->expires_at->addSecond());

    logoutWith($token)->assertUnauthorized();
});

it('rejects wrong passwords and unknown users with the same error', function ($username, $password) {
    createDoe();

    apiPost('/login', ['username' => $username, 'password' => $password])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['username' => 'Invalid credentials.']);

    expect(PersonalAccessToken::count())->toBe(0);
})->with([
    'wrong password' => ['doe', 'not-the-password'],
    'unknown user' => ['nobody', PASSWORD],
]);

it('revokes only the token it was called with', function () {
    $user = createDoe();
    $first = loginAs($user, null)->json('token');
    $second = loginAs($user, null)->json('token');

    logoutWith($first)->assertNoContent();

    logoutWith($first)->assertUnauthorized();
    logoutWith($second)->assertNoContent();
});

it('answers an anonymous logout with 401 JSON, even without an Accept header', function () {
    test()->call('POST', '/api/logout')
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthenticated.']);
});

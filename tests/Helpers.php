<?php

use App\Models\User;

const URL_A = 'https://example.com/a';
const URL_B = 'https://example.com/b';
const PASSWORD = 'password123';

function apiPost($uri, $payload, $cookies = [], $server = [])
{
    auth()->forgetGuards();

    return test()->call(
        'POST',
        "/api{$uri}",
        [],
        $cookies,
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'] + $server,
        json_encode($payload),
    );
}

function shorten($url, $guestToken = null, $user = null)
{
    return apiPost('/urls', ['original_url' => $url],
        $guestToken ? ['guest_token' => $guestToken] : [],
        $user ? ['HTTP_AUTHORIZATION' => 'Bearer '.$user->createToken('test')->plainTextToken] : []);
}

function loginAs($user, $guestToken)
{
    return apiPost(
        '/login',
        ['username' => $user->username, 'password' => PASSWORD],
        $guestToken ? ['guest_token' => $guestToken] : [],
    );
}

function createDoe()
{
    return User::factory()->create(['username' => 'doe', 'password' => bcrypt(PASSWORD)]);
}

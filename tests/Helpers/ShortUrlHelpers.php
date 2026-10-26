<?php

const URL_A = 'https://example.com/a';
const URL_B = 'https://example.com/b';

function shorten($url, $guestToken = null, $user = null)
{
    return apiPost('/urls', ['original_url' => $url],
        $guestToken ? ['guest_token' => $guestToken] : [],
        $user ? ['HTTP_AUTHORIZATION' => 'Bearer '.$user->createToken('test')->plainTextToken] : []);
}

function listLinks($guestToken = null, $user = null, $query = [])
{
    auth()->forgetGuards();

    return test()->call(
        'GET',
        '/api/urls',
        $query,
        $guestToken ? ['guest_token' => $guestToken] : [],
        [],
        ['HTTP_ACCEPT' => 'application/json']
        + ($user ? ['HTTP_AUTHORIZATION' => 'Bearer '.$user->createToken('test')->plainTextToken] : []),
    );
}

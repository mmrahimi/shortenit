<?php

const URL_A = 'https://example.com/a';
const URL_B = 'https://example.com/b';

function shorten($url, $guestToken = null, $user = null)
{
    return apiPost('/urls', ['original_url' => $url],
        $guestToken ? ['guest_token' => $guestToken] : [],
        $user ? ['HTTP_AUTHORIZATION' => 'Bearer '.$user->createToken('test')->plainTextToken] : []);
}

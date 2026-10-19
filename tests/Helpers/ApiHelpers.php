<?php

function apiPost($uri, $payload, $cookies = [], $server = [])
{
    auth()->forgetGuards();

    return test()->call(
        'POST',
        "/api$uri",
        [],
        $cookies,
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'] + $server,
        json_encode($payload),
    );
}

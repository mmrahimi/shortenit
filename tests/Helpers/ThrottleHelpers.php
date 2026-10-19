<?php

function limitOf($name)
{
    return RateLimiter::limiter($name)(Request::create('/'))->maxAttempts;
}

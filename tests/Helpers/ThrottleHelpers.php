<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

function limitOf($name)
{
    return RateLimiter::limiter($name)(Request::create('/'))->maxAttempts;
}

<?php

use App\Models\User;

const PASSWORD = 'password123';

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

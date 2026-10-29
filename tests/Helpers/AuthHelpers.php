<?php

use App\Models\User;

const PASSWORD = 'password123';
const NEW_USER = ['username' => 'user', 'password' => PASSWORD, 'password_confirmation' => PASSWORD];

function createDoe()
{
    return User::factory()->create(['username' => 'doe', 'password' => bcrypt(PASSWORD)]);
}

function loginAs($user, $guestToken)
{
    return apiPost(
        '/login',
        ['username' => $user->username, 'password' => PASSWORD],
        $guestToken ? ['guest_token' => $guestToken] : [],
    );
}

function logoutWith($token)
{
    return apiPost('/logout', [], [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
}

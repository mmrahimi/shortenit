<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;

class ClaimGuestUrlsAction
{
    public function execute($user, $guestToken)
    {
        if (! $guestToken) {
            return;
        }

        ShortUrl::where('guest_token', $guestToken)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id, 'guest_token' => null]);
    }
}

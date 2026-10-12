<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;
use App\Models\User;

class ClaimGuestUrlsAction
{
    public function execute(User $user, ?string $guestToken): void
    {
        if (! $guestToken) {
            return;
        }

        ShortUrl::where('guest_token', $guestToken)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id, 'guest_token' => null]);
    }
}

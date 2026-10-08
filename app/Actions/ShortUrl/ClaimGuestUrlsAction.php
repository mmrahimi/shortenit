<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;
use Illuminate\Support\Facades\DB;

class ClaimGuestUrlsAction
{
    public function execute($user, $guestToken)
    {
        if (! $guestToken) return;

        DB::transaction(function () use ($user, $guestToken) {
            $ownedHashes = ShortUrl::where('user_id', $user->id)->pluck('url_hash');

            ShortUrl::where('guest_token', $guestToken)
                ->whereIn('url_hash', $ownedHashes)
                ->delete();

            ShortUrl::where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id, 'guest_token' => null]);
        });
    }
}

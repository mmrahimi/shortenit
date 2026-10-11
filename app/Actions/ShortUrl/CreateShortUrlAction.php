<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class CreateShortUrlAction
{
    public function execute($user, $guestToken, $data)
    {
        $owner = $user ? ['user_id' => $user->id] : ['guest_token' => $guestToken];

        $shortUrl = ShortUrl::firstOrCreate(
            $owner + ['url_hash' => hash('sha256', $data['original_url'])],
            [
                'short_code' => Str::random(config('shortenit.short_url_length')),
                'original_url' => $data['original_url'],
            ],
        );

        return $shortUrl->short_code;
    }
}

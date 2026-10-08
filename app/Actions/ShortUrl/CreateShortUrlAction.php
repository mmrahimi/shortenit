<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class CreateShortUrlAction
{
    public function execute($user, $data)
    {
        $shortCode = Str::random(config('shortenit.short_url_length'));

        ShortUrl::create([
            'user_id' => $user?->id,
            'short_code' => $shortCode,
            'original_url' => $data['original_url'],
        ]);

        return $shortCode;
    }
}

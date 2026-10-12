<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;

class FindShortUrlAction
{
    public function execute(string $shortCode): ShortUrl
    {
        return ShortUrl::where('short_code', $shortCode)->firstOrFail();
    }
}

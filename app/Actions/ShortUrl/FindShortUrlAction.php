<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;

class FindShortUrlAction
{
    public function execute($shortCode)
    {
        return ShortUrl::where('short_code', $shortCode)->firstOrFail();
    }
}

<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;
use Illuminate\Support\Facades\Cache;
use RedisException;

class FindShortUrlAction
{
    public function execute(string $shortCode): string
    {
        try {
            return Cache::rememberForever(
                ShortUrl::redirectCacheKey($shortCode),
                fn (): string => $this->findInDatabase($shortCode),
            );
        } catch (RedisException $e) {
            report($e);

            return $this->findInDatabase($shortCode);
        }
    }

    private function findInDatabase(string $shortCode): string
    {
        return ShortUrl::where('short_code', $shortCode)->firstOrFail()->original_url;
    }
}

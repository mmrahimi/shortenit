<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrl extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function redirectCacheKey(string $shortCode): string
    {
        return "short_urls:redirect:$shortCode";
    }
}

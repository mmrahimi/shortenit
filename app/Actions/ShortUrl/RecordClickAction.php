<?php

namespace App\Actions\ShortUrl;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RecordClickAction
{
    public function execute(string $shortCode): void
    {
        try {
            DB::table('short_urls')->where('short_code', $shortCode)->increment('clicks');
        } catch (QueryException $e) {
            report($e);
        }
    }
}

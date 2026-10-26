<?php

namespace App\Http\Controllers\ShortUrl;

use App\Actions\ShortUrl\FindShortUrlAction;
use App\Actions\ShortUrl\RecordClickAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectShortUrlController extends Controller
{
    public function __invoke(Request $request, string $shortCode, FindShortUrlAction $find, RecordClickAction $recordClick): RedirectResponse
    {
        $originalUrl = $find->execute($shortCode);

        if (! $request->isMethod('HEAD')) {
            $recordClick->execute($shortCode);
        }

        return redirect($originalUrl);
    }
}

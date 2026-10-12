<?php

namespace App\Http\Controllers;

use App\Actions\ShortUrl\CreateShortUrlAction;
use App\Actions\ShortUrl\FindShortUrlAction;
use App\Http\Requests\ShortUrl\CreateShortUrlRequest;

class ShortUrlController extends Controller
{
    public function store(CreateShortUrlRequest $request, CreateShortUrlAction $action)
    {
        $shortCode = $action->execute($request->user('sanctum'), $request->attributes->get('guest_token'), $request->validated());

        return response()->json([
            'short_code' => $shortCode,
            'short_url' => route('redirect', $shortCode),
        ], 201);
    }

    public function redirect($shortCode, FindShortUrlAction $action)
    {
        $shortUrl = $action->execute($shortCode);

        return redirect($shortUrl->original_url, 301);
    }
}

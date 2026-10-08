<?php

namespace App\Http\Controllers;

use App\Actions\ShortUrl\CreateShortUrlAction;
use App\Http\Requests\ShortUrl\CreateShortUrlRequest;

class ShortUrlController extends Controller
{
    public function store(CreateShortUrlRequest $request, CreateShortUrlAction $action)
    {
        $shortUrl = $action->execute($request->user(), $request->validated());

        return response()->json([
            'short_url' => url($shortUrl),
        ], 201);
    }
}

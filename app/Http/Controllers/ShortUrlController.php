<?php

namespace App\Http\Controllers;

use App\Actions\ShortUrl\CreateShortUrlAction;
use App\Http\Requests\ShortUrl\CreateShortUrlRequest;

class ShortUrlController extends Controller
{
    public function store(CreateShortUrlRequest $request, CreateShortUrlAction $action)
    {
        $shortCode = $action->execute($request->user('sanctum'), $request->attributes->get('guest_token'), $request->validated());

        return response()->json([
            'short_url' => url($shortCode)
        ], 201);
    }
}

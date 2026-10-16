<?php

namespace App\Http\Controllers;

use App\Actions\ShortUrl\CreateShortUrlAction;
use App\Actions\ShortUrl\FindShortUrlAction;
use App\Http\Requests\ShortUrl\CreateShortUrlRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ShortUrlController extends Controller
{
    public function store(CreateShortUrlRequest $request, CreateShortUrlAction $action): JsonResponse
    {
        $shortCode = $action->execute($request->user('sanctum'), $request->attributes->get('guest_token'), $request->validated());

        return response()->json([
            'short_code' => $shortCode,
            'short_url' => route('redirect', $shortCode),
        ], 201);
    }

    public function redirect(string $shortCode, FindShortUrlAction $action): RedirectResponse
    {
        $originalUrl = $action->execute($shortCode);

        return redirect($originalUrl);
    }
}

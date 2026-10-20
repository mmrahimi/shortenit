<?php

namespace App\Http\Controllers;

use App\Actions\ShortUrl\CreateShortUrlAction;
use App\Actions\ShortUrl\FindShortUrlAction;
use App\Actions\ShortUrl\RecordClickAction;
use App\Http\Requests\ShortUrl\CreateShortUrlRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function redirect(Request $request, string $shortCode, FindShortUrlAction $find, RecordClickAction $recordClick): RedirectResponse
    {
        $originalUrl = $find->execute($shortCode);

        if (! $request->isMethod('HEAD')) {
            $recordClick->execute($shortCode);
        }

        return redirect($originalUrl);
    }
}

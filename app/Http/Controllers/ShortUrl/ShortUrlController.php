<?php

namespace App\Http\Controllers\ShortUrl;

use App\Actions\ShortUrl\CreateShortUrlAction;
use App\Actions\ShortUrl\ListShortUrlsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShortUrl\CreateShortUrlRequest;
use App\Http\Resources\ShortUrl\ShortUrlResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShortUrlController extends Controller
{
    public function index(Request $request, ListShortUrlsAction $action): AnonymousResourceCollection
    {
        return ShortUrlResource::collection($action->execute($request->user('sanctum'), $request->attributes->get('guest_token')));
    }

    public function store(CreateShortUrlRequest $request, CreateShortUrlAction $action): JsonResponse
    {
        $shortCode = $action->execute($request->user('sanctum'), $request->attributes->get('guest_token'), $request->validated());

        return response()->json([
            'short_code' => $shortCode,
            'short_url' => route('redirect', $shortCode),
        ], 201);
    }
}

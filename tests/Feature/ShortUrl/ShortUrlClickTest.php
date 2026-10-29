<?php

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Str;

it('counts each redirect, whoever makes it, on the visited link only', function () {
    $visited = shorten(URL_A, (string) Str::uuid())->json('short_code');
    $other = shorten(URL_B, (string) Str::uuid())->json('short_code');
    $token = User::factory()->create()->createToken('test')->plainTextToken;

    test()->get(route('redirect', $visited))->assertRedirect(URL_A);
    test()->withToken($token)->get(route('redirect', $visited))->assertRedirect(URL_A);

    expect(ShortUrl::firstWhere('short_code', $visited)->clicks)->toBe(2)
        ->and(ShortUrl::firstWhere('short_code', $other)->clicks)->toBe(0);
});

it('does not count HEAD requests', function () {
    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');

    test()->call('HEAD', route('redirect', $code))->assertRedirect(URL_A);

    expect(ShortUrl::firstWhere('short_code', $code)->clicks)->toBe(0);
});

it('still redirects and reports the error when counting fails', function () {
    Exceptions::fake();
    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');

    DB::beforeExecuting(function ($query) {
        if (str_contains($query, 'short_urls') && str_starts_with(strtolower($query), 'update')) {
            throw new QueryException(DB::getDefaultConnection(), $query, [], new Exception('simulated failure'));
        }
    });

    test()->get(route('redirect', $code))->assertRedirect(URL_A);

    Exceptions::assertReported(QueryException::class);
});

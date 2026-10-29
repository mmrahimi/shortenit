<?php

use App\Models\ShortUrl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Str;

it('caches the original url after the first redirect', function () {
    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');
    $key = ShortUrl::redirectCacheKey($code);

    expect(Cache::has($key))->toBeFalse();

    test()->get(route('redirect', $code))->assertRedirect(URL_A);

    expect(Cache::get($key))->toBe(URL_A);
});

it('serves repeat redirects without loading the link from the database', function () {
    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');
    $loads = 0;

    ShortUrl::retrieved(function () use (&$loads) {
        $loads++;
    });

    test()->get(route('redirect', $code))->assertStatus(302)->assertRedirect(URL_A);
    expect($loads)->toBe(1);

    test()->get(route('redirect', $code))->assertStatus(302)->assertRedirect(URL_A);
    expect($loads)->toBe(1);
});

it('does not cache unknown code', function () {
    $code = 'notfound';

    test()->get(route('redirect', $code))->assertNotFound();

    expect(Cache::has(ShortUrl::redirectCacheKey($code)))->toBeFalse();
});

it('redirects a code that was missing earlier once it exists', function () {
    test()->get(route('redirect', 'late123'))->assertNotFound();

    ShortUrl::create([
        'short_code' => 'late123',
        'original_url' => URL_A,
        'url_hash' => hash('sha256', URL_A),
    ]);

    test()->get(route('redirect', 'late123'))->assertRedirect(URL_A);
});

it('caches codes that differ only by case under separate keys', function () {
    foreach (['aBcDeF' => URL_A, 'abcdef' => URL_B] as $code => $url) {
        ShortUrl::create([
            'short_code' => $code,
            'original_url' => $url,
            'url_hash' => hash('sha256', $url),
        ]);
    }

    test()->get(route('redirect', 'aBcDeF'))->assertRedirect(URL_A);
    test()->get(route('redirect', 'abcdef'))->assertRedirect(URL_B);

    expect(Cache::get(ShortUrl::redirectCacheKey('aBcDeF')))->toBe(URL_A)
        ->and(Cache::get(ShortUrl::redirectCacheKey('abcdef')))->toBe(URL_B);
});

it('stores the redirect in redis with no expiry', function () {
    config(['cache.default' => 'redis']);
    $store = Cache::store('redis')->getStore();

    try {
        $store->connection()->ping();
    } catch (Throwable) {
        test()->markTestSkipped('Redis is not reachable.');
    }

    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');
    $key = ShortUrl::redirectCacheKey($code);

    test()->get(route('redirect', $code))->assertRedirect(URL_A);

    expect($store->connection()->ttl($store->getPrefix().$key))->toBe(-1)
        ->and(Cache::store('redis')->get($key))->toBe(URL_A);

    Cache::store('redis')->forget($key);
});

it('falls back to the database when redis is down', function () {
    Exceptions::fake();
    $code = shorten(URL_A, (string) Str::uuid())->json('short_code');

    Cache::partialMock()->shouldReceive('rememberForever')->andThrow(new RedisException('Connection refused'));

    test()->get(route('redirect', $code))->assertStatus(302)->assertRedirect(URL_A);

    Exceptions::assertReported(RedisException::class);
});

it('still returns 404 for unknown codes when redis is down', function () {
    Exceptions::fake();

    Cache::partialMock()->shouldReceive('rememberForever')->andThrow(new RedisException('Connection refused'));

    test()->get(route('redirect', 'notfound'))->assertNotFound();
});

<?php

use App\Actions\ShortUrl\CreateShortUrlAction;
use App\Exceptions\ShortCodeGenerationFailedException;
use App\Models\ShortUrl;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['shortenit.short_url_length' => 6, 'shortenit.short_url_max_length' => 8]);

    foreach ([6, 7, 8] as $length) {
        ShortUrl::create([
            'short_code' => str_repeat('a', $length),
            'original_url' => URL_B,
            'url_hash' => hash('sha256', URL_B),
        ]);
    }
});

afterEach(fn () => Str::createRandomStringsNormally());

it('retries with a new code when the generated code already exists', function () {
    $codes = ['aaaaaa', 'bbbbbb'];

    Str::createRandomStringsUsing(function (int $length) use (&$codes) {
        return $length === 6 ? array_shift($codes) : str_repeat('x', $length);
    });

    $code = shorten(URL_A, (string) Str::uuid())->assertCreated()->json('short_code');

    expect($code)->toBe('bbbbbb')
        ->and(ShortUrl::firstWhere('short_code', 'bbbbbb')->original_url)->toBe(URL_A);
});

it('escalates to a longer code after exhausting attempts at the current length', function () {
    Log::spy();
    Str::createRandomStringsUsing(fn (int $length) => str_repeat($length === 6 ? 'a' : 'b', $length));

    $code = shorten(URL_A, (string) Str::uuid())->assertCreated()->json('short_code');

    expect($code)->toBe('bbbbbbb');

    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Short code length escalated', ['from' => 6, 'to' => 7]);
});

it('returns 503 and creates nothing when every length is exhausted', function () {
    Str::createRandomStringsUsing(fn (int $length) => str_repeat('a', $length));

    shorten(URL_A, (string) Str::uuid())
        ->assertStatus(503)
        ->assertJsonPath('message', 'Could not create a short link. Please retry.');

    expect(ShortUrl::count())->toBe(3);
});

it('reports lengths, attempts and the original db error when exhausted', function () {
    Str::createRandomStringsUsing(fn (int $length) => str_repeat('a', $length));

    expect(fn () => app(CreateShortUrlAction::class)->execute(null, (string) Str::uuid(), ['original_url' => URL_A]))
        ->toThrow(function (ShortCodeGenerationFailedException $e) {
            expect($e->context())->toBe(['min_length' => 6, 'max_length' => 8, 'attempts' => 9])
                ->and($e->getPrevious())->toBeInstanceOf(UniqueConstraintViolationException::class);
        });
});

it('returns the existing code when a concurrent request wins the same-owner race', function () {
    $token = (string) Str::uuid();
    $raced = false;

    ShortUrl::creating(function () use (&$raced, $token) {
        if ($raced) {
            return;
        }

        $raced = true;

        DB::table('short_urls')->insert([
            'guest_token' => $token,
            'short_code' => 'winner1',
            'original_url' => URL_A,
            'url_hash' => hash('sha256', URL_A),
        ]);
    });

    $code = shorten(URL_A, $token)->assertCreated()->json('short_code');

    expect($code)->toBe('winner1')
        ->and(ShortUrl::where('guest_token', $token)->count())->toBe(1);
});

<?php

namespace App\Actions\ShortUrl;

use App\Exceptions\ShortCodeGenerationFailedException;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateShortUrlAction
{
    private const int ATTEMPTS_PER_LENGTH = 3;

    public function execute(?User $user, ?string $guestToken, array $data): string
    {
        $attributes = ($user ? ['user_id' => $user->id] : ['guest_token' => $guestToken])
            + ['url_hash' => hash('sha256', $data['original_url'])];

        if ($existing = $this->existingCode($attributes)) {
            return $existing;
        }

        $minLength = config('shortenit.short_code_length');
        $maxLength = max($minLength, config('shortenit.short_code_max_length'));
        $attempts = 0;
        $last = null;

        for ($length = $minLength; $length <= $maxLength; $length++) {
            for ($try = 1; $try <= self::ATTEMPTS_PER_LENGTH; $try++) {
                $attempts++;
                $code = Str::random($length);

                try {
                    return ShortUrl::create($attributes + [
                        'short_code' => $code,
                        'original_url' => $data['original_url'],
                    ])->short_code;
                } catch (UniqueConstraintViolationException $e) {
                    if ($existing = $this->existingCode($attributes)) {
                        return $existing;
                    }

                    $last = $e;
                }
            }

            if ($length < $maxLength) {
                Log::warning('Short code length escalated', ['from' => $length, 'to' => $length + 1]);
            }
        }

        throw new ShortCodeGenerationFailedException($minLength, $maxLength, $attempts, $last);
    }

    private function existingCode(array $attributes): ?string
    {
        return ShortUrl::where($attributes)->value('short_code');
    }
}

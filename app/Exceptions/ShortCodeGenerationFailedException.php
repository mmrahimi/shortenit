<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class ShortCodeGenerationFailedException extends RuntimeException
{
    public function __construct(
        private int $minLength,
        private int $maxLength,
        private int $attempts,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            "No unique short code after {$attempts} attempts (lengths {$minLength}-{$maxLength}).",
            previous: $previous,
        );
    }

    public function context(): array
    {
        return [
            'min_length' => $this->minLength,
            'max_length' => $this->maxLength,
            'attempts' => $this->attempts,
        ];
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => 'Could not create a short link. Please retry.'], 503);
    }
}

<?php

namespace App\Actions\Auth;

use App\Actions\ShortUrl\ClaimGuestUrlsAction;
use Illuminate\Support\Facades\DB;

class IssueAuthTokenAction
{
    public function __construct(private ClaimGuestUrlsAction $claimGuestUrlsAction) {}

    public function execute($user, $guestToken)
    {
        return DB::transaction(function () use ($user, $guestToken) {
            $token = $user->createToken('api-token', ['*'], now()->addHours(8))->plainTextToken;

            $this->claimGuestUrlsAction->execute($user, $guestToken);

            return $token;
        });
    }
}

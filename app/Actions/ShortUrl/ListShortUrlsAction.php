<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class ListShortUrlsAction
{
    public function execute(?User $user, ?string $guestToken): CursorPaginator
    {
        return $this->ownedBy($user, $guestToken)
            ->select(['id', 'short_code', 'original_url', 'clicks', 'created_at'])
            ->orderByDesc('id')
            ->cursorPaginate((int) config('shortenit.links_per_page'));
    }

    private function ownedBy(?User $user, ?string $guestToken): Builder
    {
        return match (true) {
            $user !== null => ShortUrl::where('user_id', $user->id),
            filled($guestToken) => ShortUrl::where('guest_token', $guestToken),
            default => ShortUrl::whereKey([]),
        };
    }
}

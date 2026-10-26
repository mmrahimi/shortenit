<?php

use App\Actions\ShortUrl\ListShortUrlsAction;
use App\Models\User;
use Illuminate\Support\Str;

it("lists only the caller's links, newest first, with click counts", function () {
    [$user, $stranger] = User::factory()->count(2)->create();

    $first = shorten(URL_A, user: $user)->json('short_code');
    $second = shorten(URL_B, user: $user)->json('short_code');

    shorten(URL_A, user: $stranger);
    shorten(URL_A, (string) Str::uuid());

    test()->get("/api/$first");
    test()->get("/api/$first");

    $links = listLinks(user: $user)->assertOk()->json('data');

    expect(array_column($links, 'short_code'))->toBe([$second, $first])
        ->and(array_column($links, 'clicks'))->toBe([0, 2])
        ->and($links[0]['short_url'])->toBe(route('redirect', $second));
});

it('shows a guest only the links tied to their cookie', function () {
    $mine = (string) Str::uuid();

    $code = shorten(URL_A, $mine)->json('short_code');

    shorten(URL_B, (string) Str::uuid());
    shorten(URL_B, user: User::factory()->create());

    $codes = array_column(listLinks($mine)->json('data'), 'short_code');

    expect($codes)->toBe([$code]);
});

it('returns an empty list instead of other peoples links when the caller has no identity', function () {
    shorten(URL_A, user: User::factory()->create());

    $page = app(ListShortUrlsAction::class)->execute(null, null);

    expect($page->items())->toBeEmpty();
});

it('pages through the links without repeating any', function () {
    config(['shortenit.links_per_page' => 2]);

    $user = User::factory()->create();
    $codes = collect(range(1, 3))->map(fn ($i) => shorten("https://example.com/$i", user: $user)->json('short_code'));

    $first = listLinks(user: $user)->assertOk();

    $second = listLinks(user: $user, query: ['cursor' => $first->json('meta.next_cursor')])->assertOk();

    expect(array_column($first->json('data'), 'short_code'))->toBe([$codes[2], $codes[1]])
        ->and(array_column($second->json('data'), 'short_code'))->toBe([$codes[0]])
        ->and($second->json('meta.next_cursor'))->toBeNull();
});

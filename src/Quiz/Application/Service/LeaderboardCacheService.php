<?php

declare(strict_types=1);

namespace App\Quiz\Application\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Small wrapper dedicated to storing the leaderboard payload in cache.
 */
class LeaderboardCacheService
{
    private const CACHE_KEY = 'quiz.leaderboard.cache';
    private const DEFAULT_TTL = 300;

    public function __construct(
        #[Autowire(service: 'leaderboard.cache')]
        private CacheInterface $leaderboardCache,
    ) {
    }

    /**
     * @param callable(): array $callback
     */
    public function remember(callable $callback): array
    {
        return $this->leaderboardCache->get(self::CACHE_KEY, static function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::DEFAULT_TTL);

            return $callback();
        });
    }

    public function invalidate(): void
    {
        $this->leaderboardCache->delete(self::CACHE_KEY);
    }
}

<?php

declare(strict_types=1);

namespace App\Quiz\Application\Service;

use App\Quiz\Domain\Entity\Score;
use App\Quiz\Infrastructure\Repository\ScoreRepository;

/**
 * Provides a read-only view of the persisted score entities ordered by score.
 */
readonly class ScoreLeaderboardQueryService
{
    public function __construct(private ScoreRepository $scoreRepository)
    {
    }

    /**
     * @return Score[]
     */
    public function fetchLeaderboard(): array
    {
        return $this->scoreRepository->findAllOrderedByScoreDesc();
    }
}

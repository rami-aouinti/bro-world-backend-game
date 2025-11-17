<?php

declare(strict_types=1);

namespace App\Quiz\Application\Service;

use App\Quiz\Domain\Entity\GameQuestion;
use App\Quiz\Domain\Entity\Score;
use App\Quiz\Application\ApiProxy\UserProxy;
use App\Quiz\Infrastructure\Repository\ScoreRepository;

/**
 * Builds the leaderboard payload while transparently using cache.
 */
readonly class LeaderboardService
{
    public function __construct(
        private ScoreRepository $scoreRepository,
        private UserProxy $userProxy,
        private LeaderboardCacheService $leaderboardCacheService,
    ) {
    }

    public function getLeaderboard(): array
    {
        return $this->leaderboardCacheService->remember(function (): array {
            $scores = $this->scoreRepository->findAllOrderedByScoreDesc();

            $leaderboard = [];

            foreach ($scores as $score) {
                $this->aggregateScore($leaderboard, $score);
            }

            $leaderboard = array_values($leaderboard);
            usort($leaderboard, static fn (array $first, array $second) => $second['score'] <=> $first['score']);

            return [
                'count' => count($leaderboard),
                'leaderboard' => $leaderboard,
            ];
        });
    }

    public function invalidateCache(): void
    {
        $this->leaderboardCacheService->invalidate();
    }

    /**
     * @param array<string, array{userId: array|null, score: int}> $leaderboard
     */
    private function aggregateScore(array &$leaderboard, Score $score): void
    {
        $game = $score->getGame();

        if ($game === null) {
            return;
        }

        $userId = $score->getUser()->toString();

        if (!isset($leaderboard[$userId])) {
            $leaderboard[$userId] = [
                'userId' => $this->userProxy->searchUser($userId),
                'score' => 0,
            ];
        }

        foreach ($game->getGameQuestions() as $gameQuestion) {
            $leaderboard[$userId]['score'] += $this->calculateWeight($gameQuestion);
        }
    }

    private function calculateWeight(GameQuestion $gameQuestion): int
    {
        if (!$gameQuestion->isIsResponse()) {
            return 0;
        }

        $difficulty = strtolower($gameQuestion->getQuestion()?->getLevel()->getLabel() ?? '');

        return match ($difficulty) {
            'medium' => 2,
            'hard' => 3,
            default => 1,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Quiz\Application\Messenger\Handler;

use App\Quiz\Application\Messenger\Message\UpdateScoreMessage;
use App\Quiz\Application\Service\LeaderboardService;
use App\Quiz\Infrastructure\Repository\ScoreRepository;
use Ramsey\Uuid\Uuid;

final class UpdateScoreMessageHandler
{
    public function __construct(
        private readonly ScoreRepository $scoreRepository,
        private readonly LeaderboardService $leaderboardService,
    ) {
    }

    public function __invoke(UpdateScoreMessage $message): void
    {
        $score = $this->scoreRepository->find(Uuid::fromString($message->scoreId));

        if ($score === null) {
            return;
        }

        $currentScore = (int) $score->getScore();
        $score->setScore((string) ($currentScore + $message->increment));

        $this->scoreRepository->save($score, true);

        $this->leaderboardService->invalidateCache();
    }
}

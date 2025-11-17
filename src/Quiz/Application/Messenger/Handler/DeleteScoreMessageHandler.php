<?php

declare(strict_types=1);

namespace App\Quiz\Application\Messenger\Handler;

use App\Quiz\Application\Messenger\Message\DeleteScoreMessage;
use App\Quiz\Application\Service\LeaderboardService;
use App\Quiz\Infrastructure\Repository\ScoreRepository;
use Ramsey\Uuid\Uuid;

final class DeleteScoreMessageHandler
{
    public function __construct(
        private readonly ScoreRepository $scoreRepository,
        private readonly LeaderboardService $leaderboardService,
    ) {
    }

    public function __invoke(DeleteScoreMessage $message): void
    {
        $score = $this->scoreRepository->find(Uuid::fromString($message->scoreId));

        if ($score === null) {
            return;
        }

        $this->scoreRepository->remove($score, true);

        $this->leaderboardService->invalidateCache();
    }
}

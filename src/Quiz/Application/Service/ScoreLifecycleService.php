<?php

declare(strict_types=1);

namespace App\Quiz\Application\Service;

use App\Quiz\Application\Messenger\Handler\CreateScoreMessageHandler;
use App\Quiz\Application\Messenger\Handler\DeleteScoreMessageHandler;
use App\Quiz\Application\Messenger\Handler\UpdateScoreMessageHandler;
use App\Quiz\Application\Messenger\Message\CreateScoreMessage;
use App\Quiz\Application\Messenger\Message\DeleteScoreMessage;
use App\Quiz\Application\Messenger\Message\UpdateScoreMessage;

/**
 * Coordinates score creation, updates and deletions via the dedicated handlers.
 */
final class ScoreLifecycleService
{
    public function __construct(
        private readonly CreateScoreMessageHandler $createScoreMessageHandler,
        private readonly UpdateScoreMessageHandler $updateScoreMessageHandler,
        private readonly DeleteScoreMessageHandler $deleteScoreMessageHandler,
    ) {
    }

    /**
     * @param array<int, array{questionId: string, isCorrect: bool}> $questions
     */
    public function createScore(string $userId, int $scoreValue, array $questions): void
    {
        ($this->createScoreMessageHandler)(new CreateScoreMessage($userId, $scoreValue, $questions));
    }

    public function incrementScore(string $scoreId, int $increment): void
    {
        ($this->updateScoreMessageHandler)(new UpdateScoreMessage($scoreId, $increment));
    }

    public function deleteScore(string $scoreId): void
    {
        ($this->deleteScoreMessageHandler)(new DeleteScoreMessage($scoreId));
    }
}

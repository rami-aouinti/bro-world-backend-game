<?php

declare(strict_types=1);

namespace App\Quiz\Application\Service;

use App\Quiz\Application\Messenger\Message\CreateScoreMessage;
use App\Quiz\Application\Messenger\Message\DeleteScoreMessage;
use App\Quiz\Application\Messenger\Message\UpdateScoreMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Coordinates score creation, updates and deletions via the dedicated handlers.
 */
final class ScoreLifecycleService
{
    public function __construct(
        #[Autowire(service: 'messenger.bus.command_bus')]
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param array<int, array{questionId: string, isCorrect: bool}> $questions
     */
    public function createScore(string $userId, int $scoreValue, array $questions): void
    {
        $this->messageBus->dispatch(new CreateScoreMessage($userId, $scoreValue, $questions));
    }

    public function incrementScore(string $scoreId, int $increment): void
    {
        $this->messageBus->dispatch(new UpdateScoreMessage($scoreId, $increment));
    }

    public function deleteScore(string $scoreId): void
    {
        $this->messageBus->dispatch(new DeleteScoreMessage($scoreId));
    }
}

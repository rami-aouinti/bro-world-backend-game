<?php

declare(strict_types=1);

namespace App\Quiz\Application\Messenger\Message;

use Bro\WorldCoreBundle\Domain\Message\Interfaces\MessageHighInterface;

/**
 * Carries data necessary to create a new score entry.
 */
final class CreateScoreMessage implements MessageHighInterface
{
    /**
     * @param array<int, array{questionId: string, isCorrect: bool}> $questions
     */
    public function __construct(
        public readonly string $userId,
        public readonly int $score,
        public readonly array $questions,
    ) {
    }
}

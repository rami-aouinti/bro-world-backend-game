<?php

declare(strict_types=1);

namespace App\Quiz\Application\Messenger\Message;

use App\General\Domain\Message\Interfaces\MessageHighInterface;

final class UpdateScoreMessage implements MessageHighInterface
{
    public function __construct(
        public readonly string $scoreId,
        public readonly int $increment,
    ) {
    }
}

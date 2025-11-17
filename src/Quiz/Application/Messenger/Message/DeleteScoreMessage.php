<?php

declare(strict_types=1);

namespace App\Quiz\Application\Messenger\Message;

use App\General\Domain\Message\Interfaces\MessageHighInterface;

final class DeleteScoreMessage implements MessageHighInterface
{
    public function __construct(public readonly string $scoreId)
    {
    }
}

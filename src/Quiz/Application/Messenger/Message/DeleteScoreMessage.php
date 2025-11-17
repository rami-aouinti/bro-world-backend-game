<?php

declare(strict_types=1);

namespace App\Quiz\Application\Messenger\Message;

use Bro\WorldCoreBundle\Domain\Message\Interfaces\MessageHighInterface;

final class DeleteScoreMessage implements MessageHighInterface
{
    public function __construct(public readonly string $scoreId)
    {
    }
}

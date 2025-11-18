<?php

declare(strict_types=1);

namespace App\Quiz\Application\Messenger\Handler;

use App\Quiz\Application\Messenger\Message\CreateScoreMessage;
use App\Quiz\Application\Service\LeaderboardService;
use App\Quiz\Domain\Entity\Game;
use App\Quiz\Domain\Entity\GameQuestion;
use App\Quiz\Domain\Entity\Question;
use App\Quiz\Domain\Entity\Score;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command_bus')]
final class CreateScoreMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LeaderboardService $leaderboardService,
    ) {
    }

    public function __invoke(CreateScoreMessage $message): void
    {
        $score = new Score();
        $score->setScore((string) $message->score);
        $score->setUser(Uuid::fromString($message->userId));

        $game = new Game();
        $score->setGame($game);

        foreach ($message->questions as $questionData) {
            $question = $this->entityManager->getRepository(Question::class)
                ->find(Uuid::fromString($questionData['questionId']));

            if ($question === null) {
                continue;
            }

            $gameQuestion = new GameQuestion();
            $gameQuestion->setQuestion($question);
            $gameQuestion->setIsResponse($questionData['isCorrect']);

            $game->addGameQuestion($gameQuestion);
            $this->entityManager->persist($gameQuestion);
        }

        $this->entityManager->persist($score);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $this->leaderboardService->invalidateCache();
    }
}

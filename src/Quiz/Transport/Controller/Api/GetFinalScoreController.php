<?php

namespace App\Quiz\Transport\Controller\Api;

use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Quiz\Domain\Entity\Score;
use App\Quiz\Domain\Entity\GameQuestion;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class GetFinalScoreController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/platform/quiz/final-score', name: 'api_quiz_final_score', methods: ['GET'])]
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        $userId = Uuid::fromString($symfonyUser->getUserIdentifier());

        $scores = $this->em->getRepository(Score::class)->findBy(['user' => $userId]);

        $finalScore = 0;
        $totalQuestions = 0;

        foreach ($scores as $score) {
            $game = $score->getGame();
            if (!$game) {
                continue;
            }

            foreach ($game->getGameQuestions() as $gameQuestion) {
                $totalQuestions++;
                if ($gameQuestion->isIsResponse()) {
                    $difficulty = strtolower($gameQuestion->getQuestion()?->getLevel()->getLabel());
                    $weight = match ($difficulty) {
                        'medium' => 2,
                        'hard' => 3,
                        default => 1,
                    };
                    $finalScore += $weight;
                }
            }
        }

        return new JsonResponse([
            'userId' => $userId->toString(),
            'finalScore' => $finalScore,
            'questionsAnswered' => $totalQuestions
        ], Response::HTTP_OK);
    }
}

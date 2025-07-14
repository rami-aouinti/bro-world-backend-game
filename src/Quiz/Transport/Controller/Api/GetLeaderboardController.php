<?php

namespace App\Quiz\Transport\Controller\Api;

use App\Quiz\Domain\Entity\Score;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class GetLeaderboardController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/platform/quiz/leaderboard', name: 'api_quiz_leaderboard', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $scores = $this->em->getRepository(Score::class)->findAll();

        $leaderboard = [];

        foreach ($scores as $score) {
            $userId = $score->getUser()->toString();
            $game = $score->getGame();

            if (!$game) {
                continue;
            }

            foreach ($game->getGameQuestions() as $gameQuestion) {
                if ($gameQuestion->isIsResponse()) {
                    $difficulty = strtolower($gameQuestion->getQuestion()?->getLevel()->getLabel());
                    $weight = match ($difficulty) {
                        'medium' => 2,
                        'hard' => 3,
                        default => 1,
                    };

                    if (!isset($leaderboard[$userId])) {
                        $leaderboard[$userId] = [
                            'userId' => $userId,
                            'score' => 0,
                        ];
                    }

                    $leaderboard[$userId]['score'] += $weight;
                }
            }
        }

        usort($leaderboard, static fn($a, $b) => $b['score'] <=> $a['score']);

        return new JsonResponse($leaderboard, Response::HTTP_OK);
    }
}

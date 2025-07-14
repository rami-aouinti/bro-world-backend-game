<?php

namespace App\Quiz\Transport\Controller;

use App\Quiz\Domain\Entity\Score;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

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

        return new JsonResponse([
            'count' => count($scores),
            'first_score_user' => $scores[0]?->getUser()?->toString(),
            'has_game' => $scores[0]?->getGame() !== null,
            'has_questions' => count($scores[0]?->getGame()?->getGameQuestions() ?? []) > 0,
        ], Response::HTTP_OK);

    }
}

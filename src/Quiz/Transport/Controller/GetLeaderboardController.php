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

            echo "User: $userId - Game: OK\n";

            foreach ($game->getGameQuestions() as $gameQuestion) {
                $isResponse = $gameQuestion->isIsResponse() ? 'true' : 'false';
                $label = $gameQuestion->getQuestion()?->getLevel()?->getLabel();

                echo " - Question: " . $gameQuestion->getQuestion()?->getId() . " / response: $isResponse / level: $label\n";
            }
        }


        $leaderboard = array_values($leaderboard);

        usort($leaderboard, static fn($a, $b) => $b['score'] <=> $a['score']);

        return new JsonResponse([
            'count' => count($leaderboard),
            'leaderboard' => $leaderboard,
        ], Response::HTTP_OK);
    }
}

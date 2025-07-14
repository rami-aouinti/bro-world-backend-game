<?php

namespace App\Quiz\Transport\Controller;

use App\Quiz\Application\ApiProxy\UserProxy;
use App\Quiz\Domain\Entity\Score;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsController]
readonly class GetLeaderboardController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserProxy $userProxy
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
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

            if (!isset($leaderboard[$userId])) {
                $leaderboard[$userId] = [
                    'userId' => $this->userProxy->searchUser($userId),
                    'score' => 0,
                ];
            }

            foreach ($game->getGameQuestions() as $gameQuestion) {
                if ($gameQuestion->isIsResponse()) {
                    $difficulty = strtolower($gameQuestion->getQuestion()?->getLevel()->getLabel());
                    $weight = match ($difficulty) {
                        'medium' => 2,
                        'hard' => 3,
                        default => 1,
                    };

                    $leaderboard[$userId]['score'] += $weight;
                }
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

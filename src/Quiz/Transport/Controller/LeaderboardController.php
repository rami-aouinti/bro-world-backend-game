<?php

declare(strict_types=1);

namespace App\Quiz\Transport\Controller;

use App\Quiz\Application\Service\LeaderboardService;
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
readonly class LeaderboardController
{
    public function __construct(private LeaderboardService $leaderboardService)
    {
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
        return new JsonResponse($this->leaderboardService->getLeaderboard(), Response::HTTP_OK);
    }
}

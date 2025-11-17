<?php

declare(strict_types=1);

namespace App\Quiz\Transport\Controller\Api;

use App\Quiz\Application\Service\ScoreLifecycleService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
readonly class DeleteScoreController
{
    public function __construct(private ScoreLifecycleService $scoreLifecycleService)
    {
    }

    #[Route('/platform/quiz/score/{scoreId}', name: 'delete_score', methods: ['DELETE'])]
    public function __invoke(string $scoreId): JsonResponse
    {
        $this->scoreLifecycleService->deleteScore($scoreId);

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}

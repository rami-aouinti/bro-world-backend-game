<?php

declare(strict_types=1);

namespace App\Quiz\Transport\Controller\Api;

use App\Quiz\Application\Service\ScoreLifecycleService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
readonly class SubmitScoreController
{
    public function __construct(private ScoreLifecycleService $scoreLifecycleService)
    {
    }

    #[Route('/platform/quiz/submit-score', name: 'submit_score', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->request->all();

        $userId = $data['userId'] ?? null;
        $scoreValue = $data['score'] ?? null;
        $questions = $data['questions'] ?? [];

        if (!$userId || !is_numeric($scoreValue) || !is_array($questions)) {
            return new JsonResponse(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $normalizedQuestions = [];
        foreach ($questions as $question) {
            if (!isset($question['questionId'])) {
                continue;
            }

            $normalizedQuestions[] = [
                'questionId' => (string) $question['questionId'],
                'isCorrect' => (bool) ($question['isCorrect'] ?? false),
            ];
        }

        $this->scoreLifecycleService->createScore((string) $userId, (int) $scoreValue, $normalizedQuestions);

        return new JsonResponse(['message' => 'Score creation scheduled'], Response::HTTP_ACCEPTED);
    }
}

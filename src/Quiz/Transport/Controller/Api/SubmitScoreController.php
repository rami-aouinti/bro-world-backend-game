<?php

namespace App\Quiz\Transport\Controller\Api;


use App\Quiz\Domain\Entity\Game;
use App\Quiz\Domain\Entity\GameQuestion;
use App\Quiz\Domain\Entity\Question;
use App\Quiz\Domain\Entity\Score;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class SubmitScoreController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
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
        $score = new Score();
        $score->setScore((string) $scoreValue);
        $score->setUser(Uuid::fromString($userId));

        $game = new Game();
        $game->setScore($score);

        foreach ($questions as $q) {
            $questionId = $q['questionId'] ?? null;
            $isCorrect = $q['isCorrect'] ?? false;

            if (!$questionId) {
                continue;
            }

            /** @var Question|null $question */
            $question = $this->em->getRepository(Question::class)->find(Uuid::fromString($questionId));

            if (!$question) {
                continue;
            }

            $gameQuestion = new GameQuestion();
            $gameQuestion->setQuestion($question);
            $gameQuestion->setIsResponse((bool) $isCorrect);
            $gameQuestion->setGame($game);
            // Ajoute proprement la relation bidirectionnelle
            $game->addGameQuestion($gameQuestion);

            $this->em->persist($gameQuestion);
        }

        $this->em->persist($score);
        $this->em->persist($game);
        $this->em->flush();

        return new JsonResponse(['message' => 'Score saved successfully'], Response::HTTP_CREATED);
    }
}

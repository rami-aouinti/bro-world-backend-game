<?php

namespace App\Quiz\Transport\Controller;

use App\General\Domain\Utils\JSON;
use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Level;
use App\Quiz\Infrastructure\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[AsController]
#[OA\Tag(name: 'Quiz')]
readonly class QuizController
{
    public function __construct(
        private QuestionRepository     $questionRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @throws Throwable
     * @throws JsonException
     */
    #[Route(
        path: '/platform/quiz/generate',
        name: 'api_quiz_generate',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $levelId = $request->query->get('level');

        $category = $this->em->getRepository(Category::class)->find(Uuid::fromString($categoryId));
        $level = $this->em->getRepository(Level::class)->find(Uuid::fromString($levelId));

        if (!$category || !$level) {
            return new JsonResponse([
                'error' => 'Invalid category or level.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $questions = $this->questionRepository->findBy([
            'category' => $category,
            'level' => $level
        ]);

        $data = [];

        foreach ($questions as $question) {
            $answers = [];

            foreach ($question->getAnswers() as $answer) {
                $answers[] = [
                    'id' => $answer->getId(),
                    'answer' => $answer->getAnswer(),
                    'isTrue' => $answer->isIsTrue(),
                ];
            }

            $data[] = [
                'id' => $question->getId(),
                'question' => $question->getQuestion(),
                'category' => $question->getCategory()->getName(),
                'level' => $question->getLevel()->getLabel(),
                'answers' => $answers,
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
}

<?php

namespace App\Quiz\Transport\Controller;

use App\General\Domain\Utils\JSON;
use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Level;
use App\Quiz\Domain\Entity\Question;
use App\Quiz\Infrastructure\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

/**
 * Class QuizController
 * @package App\Quiz\Transport\Controller
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsController]
#[OA\Tag(name: 'Quiz')]
readonly class QuizController
{
    public function __construct(
        private QuestionRepository     $questionRepository,
        private SerializerInterface    $serializer,
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
    #[OA\Parameter(name: 'level', in: 'query', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'category', in: 'query', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: 200,
        description: 'Returns a generated quiz with questions and answers',
        content: new JsonContent(
            type: 'array',
            items: new OA\Items(ref: Question::class)
        )
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $levelId = $request->query->get('level');

        $category = $this->em->getRepository(Category::class)->find($categoryId);
        $level = $this->em->getRepository(Level::class)->find($levelId);

        if (!$category || !$level) {
            return new JsonResponse([
                'error' => 'Invalid category or level.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $questions = $this->questionRepository->findBy([
            'category' => $category,
            'level' => $level,
        ], limit: 10);

        $questionItems = [];

        foreach ($questions as $question) {
            $answers = [];
            foreach ($question->getAnswer() as $answer) {
                $answers[] = $answer;
            }

            $questionItems[] = [
                'question' => $question,
                'answers' => $answers,
            ];
        }

        return new JsonResponse(
            JSON::decode($this->serializer->serialize($questionItems, 'json')),
            Response::HTTP_OK
        );
    }
}

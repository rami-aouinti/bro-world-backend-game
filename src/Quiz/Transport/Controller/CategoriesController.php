<?php

namespace App\Quiz\Transport\Controller;

use App\Quiz\Domain\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'Quiz')]
readonly class CategoriesController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @return JsonResponse
     */
    #[Route(
        path: '/platform/categories',
        name: 'api_quiz_categories',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->em->getRepository(Category::class)->findAll(), Response::HTTP_OK);
    }
}

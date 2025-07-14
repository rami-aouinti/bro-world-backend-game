<?php

namespace App\Quiz\Transport\Controller;

use App\General\Domain\Utils\JSON;
use App\Quiz\Domain\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[OA\Tag(name: 'Quiz')]
readonly class CategoriesController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * @return JsonResponse
     * @throws \JsonException
     */
    #[Route(
        path: '/platform/categories',
        name: 'api_quiz_categories',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(): JsonResponse
    {
        $output = JSON::decode(
            $this->serializer->serialize(
                $this->em->getRepository(Category::class)->findAll(),
                'json',
                [
                    'groups' => 'Category',
                ]
            ),
            true,
        );
        return new JsonResponse($output, Response::HTTP_OK);
    }
}

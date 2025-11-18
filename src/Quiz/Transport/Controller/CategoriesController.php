<?php

namespace App\Quiz\Transport\Controller;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use App\Quiz\Domain\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsController]
#[OA\Tag(name: 'Quiz')]
readonly class CategoriesController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private CacheInterface $cache
    ) {
    }

    /**
     * @return JsonResponse
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    #[Route(
        path: '/platform/categories',
        name: 'api_quiz_categories',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(): JsonResponse
    {
        $output = $this->cache->get('quiz.categories.list', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            return JSON::decode(
                $this->serializer->serialize(
                    $this->em->getRepository(Category::class)->findAll(),
                    'json',
                    [
                        'groups' => 'Category',
                    ]
                ),
                true,
            );
        });
        return new JsonResponse($output, Response::HTTP_OK);
    }
}

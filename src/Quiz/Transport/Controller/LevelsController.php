<?php

namespace App\Quiz\Transport\Controller;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use App\Quiz\Domain\Entity\Level;
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
readonly class LevelsController
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
        path: '/platform/levels',
        name: 'api_quiz_levels',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(): JsonResponse
    {
        $output = $this->cache->get('quiz.levels.list', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            return JSON::decode(
                $this->serializer->serialize(
                    $this->em->getRepository(Level::class)->findAll(),
                    'json',
                    [
                        'groups' => 'Level',
                    ]
                ),
                true,
            );
        });
        return new JsonResponse($output, Response::HTTP_OK);
    }
}

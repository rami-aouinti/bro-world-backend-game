<?php

namespace App\Quiz\Transport\Controller;

use App\General\Domain\Utils\JSON;
use App\Quiz\Domain\Entity\Level;
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
readonly class LevelsController
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
        path: '/platform/levels',
        name: 'api_quiz_levels',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(): JsonResponse
    {
        $output = JSON::decode(
            $this->serializer->serialize(
                $this->em->getRepository(Level::class)->findAll(),
                'json',
                [
                    'groups' => 'Level',
                ]
            ),
            true,
        );
        return new JsonResponse($output, Response::HTTP_OK);
    }
}

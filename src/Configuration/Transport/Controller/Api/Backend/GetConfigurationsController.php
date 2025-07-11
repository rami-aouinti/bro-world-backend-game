<?php

declare(strict_types=1);

namespace App\Configuration\Transport\Controller\Api\Backend;

use App\Configuration\Domain\Entity\Enum\FlagType;
use App\General\Domain\Utils\JSON;
use App\Configuration\Domain\Entity\Configuration;
use App\Configuration\Domain\Repository\Interfaces\ConfigurationRepositoryInterface;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use JsonException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @package App\Configuration
 */
#[AsController]
#[OA\Tag(name: 'Configuration')]
readonly class GetConfigurationsController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ConfigurationRepositoryInterface $repository
    ) {
    }

    /**
     * Get current user Configuration data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws NotSupported
     */
    #[Route(
        path: '/v1/admin/configuration',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(SymfonyUser $symfonyUser): JsonResponse
    {
        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $this->repository->findBy([
                    'flagType' => FlagType::PROTECTED_SYSTEM,
                ]),
                'json',
                [
                    'groups' => 'Configuration',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}

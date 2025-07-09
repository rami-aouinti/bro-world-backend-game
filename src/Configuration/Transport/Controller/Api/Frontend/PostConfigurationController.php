<?php

declare(strict_types=1);

namespace App\Configuration\Transport\Controller\Api\Frontend;

use App\Configuration\Domain\Entity\Enum\FlagType;
use App\Configuration\Domain\Repository\Interfaces\ConfigurationRepositoryInterface;
use App\General\Domain\Rest\UuidHelper;
use App\General\Domain\Utils\JSON;
use App\Configuration\Domain\Entity\Configuration;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use OpenApi\Attributes\Property;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Configuration
 */
#[AsController]
#[OA\Tag(name: 'Configuration')]
readonly class PostConfigurationController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private ConfigurationRepositoryInterface $repository
    ) {
    }

    /**
     * Get current user Configuration data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws Exception
     */
    #[Route(
        path: '/v1/platform/configuration',
        methods: [Request::METHOD_POST],
    )]
    #[OA\Response(
        response: 200,
        description: 'Configuration data',
        content: new JsonContent(
            ref: new Model(
                type: Configuration::class,
                groups: ['Configuration'],
            ),
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid token (not found or expired)',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 401,
                'message' => 'JWT Token not found',
            ],
        ),
    )]
    #[OA\Response(
        response: 403,
        description: 'Access denied',
        content: new JsonContent(
            properties: [
                new Property(property: 'code', description: 'Error code', type: 'integer'),
                new Property(property: 'message', description: 'Error description', type: 'string'),
            ],
            type: 'object',
            example: [
                'code' => 403,
                'message' => 'Access denied',
            ],
        ),
    )]
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        $configuration = $this->repository->findOneBy([
            'configurationKey' => $request->request->get('configurationKey'),
            'userId' => $symfonyUser->getUserIdentifier()
        ]);

        if(!$configuration) {
            $configuration = new Configuration();
            $configuration->setUserId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $configuration->setConfigurationKey($request->request->get('configurationKey'));
            $configuration->setContextId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $configuration->setContextKey($request->request->get('contextKey'));
            $configuration->setWorkplaceId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $configuration->setFlags([FlagType::USER->value]);
        }
        $configArray = $request->request->all();
        $configuration->setConfigurationValue($configArray['configurationValue']);

        $this->entityManager->persist($configuration);
        $this->entityManager->flush();

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $configuration,
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

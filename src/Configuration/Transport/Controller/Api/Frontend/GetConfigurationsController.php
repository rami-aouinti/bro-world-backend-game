<?php

declare(strict_types=1);

namespace App\Configuration\Transport\Controller\Api\Frontend;

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
        private ConfigurationRepositoryInterface $repository,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * Get current user Configuration data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws NotSupported
     */
    #[Route(
        path: '/v1/platform/configuration',
        methods: [Request::METHOD_GET],
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
        $authorization = $request->headers->get('Authorization');

        $blogConfigurations = $this->repository->findOneBy([
            'userId' => $symfonyUser->getUserIdentifier(),
            'configurationKey' => 'blog',
        ]);

        if(!$blogConfigurations) {
            $blogConfigurations = new Configuration();
            $blogConfigurations->setUserId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $blogConfigurations->setConfigurationKey('blog');
            $blogConfigurations->setContextId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $blogConfigurations->setContextKey('blog');
            $blogConfigurations->setWorkplaceId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $blogConfigurations->setFlags([FlagType::USER->value]);

            try {
                $media = $this->createMedia($authorization, 'blog', $symfonyUser->getUserIdentifier() . '/blog/');
                $blogConfigurations->setConfigurationValue($media);
            } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            }

            $this->entityManager->persist($blogConfigurations);
            $this->entityManager->flush();
        }

        $webspaceConfigurations = $this->repository->findOneBy([
            'userId' => $symfonyUser->getUserIdentifier(),
            'configurationKey' => 'folder',
        ]);

        if(!$webspaceConfigurations) {
            $webspaceConfigurations = new Configuration();
            $webspaceConfigurations->setUserId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $webspaceConfigurations->setConfigurationKey('folder');
            $webspaceConfigurations->setContextId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $webspaceConfigurations->setContextKey('folder');
            $webspaceConfigurations->setWorkplaceId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $webspaceConfigurations->setFlags([FlagType::USER->value]);
            try {
                $media = $this->createMedia($authorization, 'folder', $symfonyUser->getUserIdentifier() . '/folder/');
                $webspaceConfigurations->setConfigurationValue($media);
            } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            }
            $this->entityManager->persist($webspaceConfigurations);
            $this->entityManager->flush();
        }

        $avatarConfigurations = $this->repository->findOneBy([
            'userId' => $symfonyUser->getUserIdentifier(),
            'configurationKey' => 'avatar',
        ]);

        if(!$avatarConfigurations) {
            $avatarConfigurations = new Configuration();
            $avatarConfigurations->setUserId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $avatarConfigurations->setConfigurationKey('avatar');
            $avatarConfigurations->setContextId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $avatarConfigurations->setContextKey('avatar');
            $avatarConfigurations->setWorkplaceId(Uuid::fromString($symfonyUser->getUserIdentifier()));
            $avatarConfigurations->setFlags([FlagType::USER->value]);
            try {
                $media = $this->createMedia($authorization, 'avatar', $symfonyUser->getUserIdentifier() . '/avatar/');
                $avatarConfigurations->setConfigurationValue($media);
            } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            }
            $this->entityManager->persist($avatarConfigurations);
            $this->entityManager->flush();
        }

        $configurations = $this->repository->findBy([
            'userId' => $symfonyUser->getUserIdentifier(),
        ]);
        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $configurations,
                'json',
                [
                    'groups' => 'Configuration',
                ]
            ),
            true,
        );

        return new JsonResponse($output);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function createMedia ($authorization, $title, $path): array
    {
        $mediaFolderResponse = $this->httpClient->request('POST', 'http://media.bro-world.org/api/v1/platform/mediaFolder', [
            'headers' => [
                'Authorization' => $authorization,
            ],
            'json' => [
                'title' => $title,
                'path' => $path,
            ],
        ]);

        return $mediaFolderResponse->toArray();
    }
}

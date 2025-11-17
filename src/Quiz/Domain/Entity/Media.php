<?php

declare(strict_types=1);

namespace App\Quiz\Domain\Entity;

use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * @package App\Blog\Domain\Entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'question_media')]
class Media
{
    use Uuid;
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false
    )]
    #[Groups(['Media', 'Question_Show', 'Question'])]
    private UuidInterface $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Groups(['Media', 'Question'])]
    private string $url;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Groups(['Media', 'Question'])]
    private string $type;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'mediaEntities')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Question $question;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setPost(Question $question): self
    {
        $this->question = $question;

        return $this;
    }
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'path' => $this->getUrl()
        ];
    }
}

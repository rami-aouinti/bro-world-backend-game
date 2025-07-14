<?php

namespace App\Quiz\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Quiz\Infrastructure\Repository\GameQuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * Class GameQuestion
 * @package App\Quiz\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: GameQuestionRepository::class)]
class GameQuestion implements EntityInterface
{
    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'GameQuestion',
        'GameQuestion.id',
    ])]
    private UuidInterface $id;

    #[ORM\Column]
    #[Groups([
        'GameQuestion',
        'GameQuestion.isResponse',
    ])]
    private ?bool $isResponse = null;

    #[ORM\ManyToOne(inversedBy: 'Question_id')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        'GameQuestion',
        'GameQuestion.game',
    ])]
    private ?Game $game = null;

    #[ORM\ManyToOne(inversedBy: 'Game')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        'GameQuestion',
        'GameQuestion.question',
    ])]
    private ?Question $question = null;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
    }


    public function getId(): string
    {
        return $this->id->toString();
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function isIsResponse(): ?bool
    {
        return $this->isResponse;
    }

    public function setIsResponse(bool $isResponse): self
    {
        $this->isResponse = $isResponse;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }
}

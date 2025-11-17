<?php

namespace App\Quiz\Domain\Entity;

use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use App\Quiz\Infrastructure\Repository\ScoreRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * Class Score
 * @package App\Quiz\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: ScoreRepository::class)]
class Score implements EntityInterface
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
        'Score',
        'Score.id',
    ])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups([
        'Score',
        'Score.score',
    ])]
    private ?string $score = null;

    #[Groups([
        'Score',
        'Score.game',
    ])]
    #[ORM\OneToOne(inversedBy: 'score', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\Column(type: 'uuid')]
    #[Groups(['Score', 'Score.user'])]
    private UuidInterface $user;

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
        return $this->getScore();
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(Game $game): self
    {
        if ($game->getScore() !== $this) {
            $game->setScore($this);
        }

        $this->game = $game;

        return $this;
    }

    public function getUser(): UuidInterface
    {
        return $this->user;
    }

    public function setUser(UuidInterface $user): void
    {
        $this->user = $user;
    }
}

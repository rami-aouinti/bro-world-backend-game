<?php

namespace App\Quiz\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Quiz\Infrastructure\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game implements EntityInterface
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
        'Game',
        'Game.id',
    ])]
    private UuidInterface $id;

    #[Groups([
        'Game',
        'Game.score',
    ])]
    #[ORM\OneToOne(mappedBy: 'game', targetEntity: Score::class, cascade: ['persist', 'remove'])]
    private ?Score $score = null;

    #[ORM\OneToMany(mappedBy: 'Game', targetEntity: GameQuestion::class)]
    #[Groups([
        'Game',
        'Game.gameQuestions',
    ])]
    private Collection $gameQuestions;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->gameQuestions = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getScore(): ?Score
    {
        return $this->score;
    }

    public function setScore(Score $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return Collection<int, GameQuestion>
     */

    public function getGameQuestions(): Collection
    {
        return $this->gameQuestions;
    }

    public function addGameQuestion(GameQuestion $gameQuestion): self
    {
        if (!$this->gameQuestions->contains($gameQuestion)) {
            $this->gameQuestions[] = $gameQuestion;
            $gameQuestion->setGame($this); // important !
        }

        return $this;
    }

    public function removeGameQuestion(GameQuestion $gameQuestion): self
    {
        if ($this->gameQuestions->removeElement($gameQuestion)) {
            if ($gameQuestion->getGame() === $this) {
                $gameQuestion->setGame(null);
            }
        }

        return $this;
    }
}

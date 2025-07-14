<?php

namespace App\Quiz\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\Quiz\Infrastructure\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * Class Question
 * @package App\Quiz\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question implements EntityInterface
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
        'Question',
        'Question.id',
    ])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups([
        'Question',
        'Question.question',
    ])]
    private ?string $question = null;

    #[ORM\OneToMany(mappedBy: 'question_id', targetEntity: Answer::class)]
    #[Groups([
        'Question',
        'Question.answer',
    ])]
    private Collection $answer;

    #[ORM\OneToMany(mappedBy: 'Question', targetEntity: GameQuestion::class)]
    #[Groups([
        'Question',
        'Question.gameQuestions',
    ])]
    private Collection $gameQuestions;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Level $level;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Media::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups([
        'Question',
    ])]
    private Collection $medias;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->answer = new ArrayCollection();
        $this->gameQuestions = new ArrayCollection();
        $this->medias = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function __toString(): string
    {
        return $this->getQuestion();
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getLevel(): Level
    {
        return $this->level;
    }

    public function setLevel(Level $level): void
    {
        $this->level = $level;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswer(): Collection
    {
        return $this->answer;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answer->contains($answer)) {
            $this->answer->add($answer);
            $answer->setQuestionId($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answer->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestionId() === $this) {
                $answer->setQuestionId(null);
            }
        }

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
            $this->gameQuestions->add($gameQuestion);
            $gameQuestion->setQuestion($this);
        }

        return $this;
    }

    public function removeGameQuestion(GameQuestion $gameQuestion): self
    {
        if ($this->gameQuestions->removeElement($gameQuestion)) {
            // set the owning side to null (unless already changed)
            if ($gameQuestion->getQuestion() === $this) {
                $gameQuestion->setQuestion(null);
            }
        }

        return $this;
    }

    public function getMediaEntities(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias[] = $media;
            $media->setPost($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->removeElement($media)) {
            // break the association
            if ($media->getQuestion() === $this) {
                $media->setPost(null);
            }
        }

        return $this;
    }
}

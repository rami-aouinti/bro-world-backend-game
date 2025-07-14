<?php

namespace App\Quiz\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * Class UserAnswer
 * @package App\Quiz\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
class UserAnswer implements EntityInterface
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
        'Quiz',
        'Quiz.id',
    ])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'userAnswers')]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    private Question $question;

    #[ORM\ManyToOne(targetEntity: Answer::class)]
    private Answer $selectedAnswer;

    #[ORM\Column(type: 'boolean')]
    private bool $isCorrect;

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

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): self
    {
        $this->game = $game;
        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getSelectedAnswer(): Answer
    {
        return $this->selectedAnswer;
    }

    public function setSelectedAnswer(Answer $answer): self
    {
        $this->selectedAnswer = $answer;
        return $this;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): self
    {
        $this->isCorrect = $isCorrect;
        return $this;
    }
}

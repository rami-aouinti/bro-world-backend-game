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
 * Class Quiz
 * @package App\Quiz\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
class Quiz implements EntityInterface
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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $template;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nbPics;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $answerLabel;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $imageLabel;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $small;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $hideTitle;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $badges;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $icon;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    public function getNbPics(): ?int
    {
        return $this->nbPics;
    }

    public function setNbPics(?int $nbPics): void
    {
        $this->nbPics = $nbPics;
    }

    public function getAnswerLabel(): ?string
    {
        return $this->answerLabel;
    }

    public function setAnswerLabel(?string $answerLabel): void
    {
        $this->answerLabel = $answerLabel;
    }

    public function getImageLabel(): ?string
    {
        return $this->imageLabel;
    }

    public function setImageLabel(?string $imageLabel): void
    {
        $this->imageLabel = $imageLabel;
    }

    public function getSmall(): ?bool
    {
        return $this->small;
    }

    public function setSmall(?bool $small): void
    {
        $this->small = $small;
    }

    public function getHideTitle(): ?bool
    {
        return $this->hideTitle;
    }

    public function setHideTitle(?bool $hideTitle): void
    {
        $this->hideTitle = $hideTitle;
    }

    public function getBadges(): ?string
    {
        return $this->badges;
    }

    public function setBadges(?string $badges): void
    {
        $this->badges = $badges;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }
}

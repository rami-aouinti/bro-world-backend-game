<?php

namespace App\Quiz\Domain\Entity;

use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * Class Category
 * @package App\Quiz\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
class Category implements EntityInterface
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
        'Category',
        'Category.id',
    ])]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups([
        'Category',
        'Category.name',
    ])]
    private string $name;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups([
        'Category',
        'Category.logo'
    ])]
    private ?string $logo = null;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }
}

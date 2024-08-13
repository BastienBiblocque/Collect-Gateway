<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
class Shop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?string $addressId = null;


    #[ORM\Column(length: 255)]
    private int $creatorId;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?string $paymentId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $activitySector = null;

    #[ORM\Column(type: 'string', length: 14, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 14,
        max: 14,
        exactMessage: 'Le numéro SIRET doit comporter exactement {{ limit }} chiffres.'
    )]
    #[Assert\Regex(
        pattern: '/^\d{14}$/',
        message: 'Le numéro SIRET doit être composé uniquement de 14 chiffres.'
    )]
    private ?string $siretNumber = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $theme = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getAddressId(): ?string
    {
        return $this->addressId;
    }

    public function setAddressId(?string $addressId): void
    {
        $this->addressId = $addressId;
    }

    public function getCreatorId(): int

    {
        return $this->creatorId;
    }

    public function setCreatorId(int $creatorId): void
    {
        $this->creatorId = $creatorId;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    public function getActivitySector(): string
    {
        return $this->activitySector;
    }

    public function setActivitySector(string $activitySector): void
    {
        $this->activitySector = $activitySector;
    }

    public function getSiretNumber(): ?string
    {
        return $this->siretNumber;
    }

    public function setSiretNumber(?string $siretNumber): void
    {
        $this->siretNumber = $siretNumber;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }
}

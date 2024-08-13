<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ShopDTO
{
    /**
     * @Assert\NotBlank()
     */
    private ?int $id = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private string $name;

    private ?string $description = null;

    private ?string $addressId = null;

    /**
     * @Assert\NotBlank()
     */
    private string $creator;

    private ?string $paymentId = null;

    private ?string $activitySector = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=14, max=14, exactMessage="Le numéro SIRET doit comporter exactement 14 chiffres.")
     * @Assert\Regex(pattern="/^\d{14}$/", message="Le numéro SIRET doit être composé uniquement de 14 chiffres.")
     */
    private ?string $siretNumber = null;

    private ?string $theme = null;

    // Getters and Setters

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

    public function getCreator(): string
    {
        return $this->creator;
    }

    public function setCreator(string $creator): void
    {
        $this->creator = $creator;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    public function getActivitySector(): ?string
    {
        return $this->activitySector;
    }

    public function setActivitySector(?string $activitySector): void
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

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): void
    {
        $this->theme = $theme;
    }

}
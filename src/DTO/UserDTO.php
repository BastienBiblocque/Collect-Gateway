<?php


namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private ?string $email = null;

    private ?string $shopId = null;

    /**
     * @Assert\NotBlank()
     */
    private array $roles = [];

    /**
     * @Assert\NotBlank()
     */
    private ?string $password = null;

    /**
     * @Assert\NotBlank()
     */
    private ?string $firstname = null;

    /**
     * @Assert\NotBlank()
     */
    private ?string $lastname = null;

    // Getters and Setters

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getShopId(): ?string
    {
        return $this->shopId;
    }

    public function setShopId(?string $shopId): void
    {
        $this->shopId = $shopId;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }
}

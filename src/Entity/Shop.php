<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
class Shop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[Groups(['shop:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['shop:read'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['shop:read'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['shop:read'])]
    private ?string $address = null;

    #[ORM\OneToMany(targetEntity: 'Product', mappedBy: 'shop')]
    #[Groups(['shop:read'])]
    private Collection $products;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'shop')]
    #[ORM\JoinColumn(unique: true, nullable: false)] // Défini la colonne de jointure ici
    #[Groups(['shop:read'])]
    private ?User $creator = null;


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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): void
    {
        $this->products = $products;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }
}

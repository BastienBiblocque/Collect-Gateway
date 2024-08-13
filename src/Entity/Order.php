<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $cartId = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $userId = null;

    #[ORM\Column(length: 255)]
    private ?string $develieryId = null;

    #[ORM\Column(length: 255)]
    private ?string $billingId = null;

    #[ORM\Column(length: 255)]
    private string $shopId;

    private ?Cart $cart = null;

    private ?Address $billingAddress = null;
    private ?Address $deliveryAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCartId(): ?string
    {
        return $this->cartId;
    }

    public function setCartId(string $cartId): static
    {
        $this->cartId = $cartId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getDevelieryId(): ?string
    {
        return $this->develieryId;
    }

    public function setDevelieryId(string $develieryId): static
    {
        $this->develieryId = $develieryId;

        return $this;
    }

    public function getBillingId(): ?string
    {
        return $this->billingId;
    }

    public function setBillingId(string $billingId): static
    {
        $this->billingId = $billingId;

        return $this;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function setShopId(string $shopId): void
    {
        $this->shopId = $shopId;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): void
    {
        $this->cart = $cart;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }
}

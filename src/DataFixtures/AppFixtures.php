<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {

        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("user@example.com");
        $encodedPassword = $this->passwordHasher->hashPassword($user, 'password');

        $user->setPassword($encodedPassword);
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);

        $product = new Product();
        $product->setName("Product 1");
        $product->setDescription("Product 1 description");
        $product->setPrice(100);
        $product->setQuantity(4);
        $manager->persist($product);

        $manager->flush();
    }
}

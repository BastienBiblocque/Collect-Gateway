<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Shop;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {

        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $user = new User();
        $user->setEmail("user@example.com");
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

//        $shop = new Shop();
//        $shop->setName($faker->company);
//        $shop->setDescription($faker->optional()->paragraph);
//        $shop->setAddress($faker->optional()->address);
//        $shop->setCreator($user);
//        $manager->persist($shop);



//        $product = new Product();
//        $product->setName("Product 1");
//        $product->setDescription("Product 1 description");
//        $product->setPrice(100);
//        $product->setQuantity(4);
//        $product->setShop($shop);
//        $manager->persist($product);

        $manager->flush();
    }
}

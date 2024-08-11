<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $passwordHasher;

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
        $manager->flush();

        $manager->flush();
    }
}

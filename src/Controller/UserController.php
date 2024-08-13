<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/new', name: 'user_new', methods: ['POST'])]
    public function new(Request $request,EntityManagerInterface $entityManager ,UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $userDTO = $serializer->deserialize($data, UserDTO::class, 'json');

        $errors = $validator->validate($userDTO);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $userRepository->findOneBy(['email' => $userDTO->getEmail()]);
        if ($existingUser) {
            return $this->json([
                'error' => 'Un utilisateur avec cet email existe déjà.'
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($userDTO->getEmail());
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $userDTO->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED);
    }
}

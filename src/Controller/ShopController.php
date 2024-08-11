<?php

// src/Controller/ShopController.php

namespace App\Controller;

use App\Entity\Shop;
use App\Form\ShopType;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/private/shop')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'get_shop', methods: ['GET'])]

    public function getAllShops(ShopRepository $shopRepository): JsonResponse
    {
        // 1. Récupérer tous les shops depuis la base de données
        $shops = $shopRepository->findAll();

        // 2. Retourner une réponse JSON avec les données des shops
        return $this->json($shops, Response::HTTP_OK, [], ['groups' => 'shop:read']);
    }

    #[Route('/new', name: 'create_shop', methods: ['POST'])]
    public function createShop(UserRepository $userRepository, Request $request, ShopRepository $shopRepository, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        // 1. Recevoir les données de la requête
        $data = json_decode($request->getContent(), true);

        // 2. Créer une nouvelle instance de Shop
        $shop = new Shop();
        $shop->setName($data['name']);
        $creator = $userRepository->find($data['creator']);
        $shop->setCreator($creator);
        // Tu peux ajouter d'autres champs ici si nécessaire

        // 3. Valider les données
        $errors = $validator->validate($shop);
        if (count($errors) > 0) {
            // Retourner les erreurs de validation
            return $this->json([
                'status' => 'error',
                'errors' => (string) $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        // 4. Sauvegarder l'entité dans la base de données
        $entityManager->persist($shop);
        $entityManager->flush();

        // 5. Retourner une réponse JSON
        return $this->json($shop, Response::HTTP_CREATED, [], ['groups' => 'shop:read']);
    }
}

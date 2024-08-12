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
        $shops = $shopRepository->findAll();

        return $this->json($shops, Response::HTTP_OK);
    }

    #[Route('/new', name: 'create_shop', methods: ['POST'])]
    public function createShop(UserRepository $userRepository, Request $request, ShopRepository $shopRepository, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $shop = new Shop();
        $shop->setName($data['name']);
        $creator = $userRepository->find($data['creator']);
        $shop->setCreatorId($creator);
        $errors = $validator->validate($shop);
        if (count($errors) > 0) {
            return $this->json([
                'status' => 'error',
                'errors' => (string) $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($shop);
        $entityManager->flush();

        return $this->json($shop, Response::HTTP_OK);
    }
}

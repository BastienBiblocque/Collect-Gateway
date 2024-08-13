<?php

// src/Controller/ShopController.php

namespace App\Controller;

use App\DTO\ShopDTO;
use App\Entity\Shop;
use App\Repository\OrderRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\isEmpty;

#[Route('/api/private/shop')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'get_shop', methods: ['GET'])]

    public function getAllShops(ShopRepository $shopRepository): JsonResponse
    {
        $shops = $shopRepository->findAll();

        return $this->json($shops, Response::HTTP_OK);
    }

    /**
     * @throws RandomException
     */
    #[Route('/new', name: 'create_shop', methods: ['POST'])]
    public function createShop(UserRepository $userRepository, Request $request, ShopRepository $shopRepository, ValidatorInterface $validator, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $shopDTO = $serializer->deserialize($data, ShopDTO::class, 'json');

        $errors = $validator->validate($shopDTO);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $existingShop = $shopRepository->findOneBy(['name' => $shopDTO->getName()]);
        if ($existingShop) {
            return $this->json([
                'error' => 'Une boutique avec ce nom existe déjà.'
            ], Response::HTTP_CONFLICT);
        }

        $shop = new Shop();
        $shop->setName($shopDTO->getName());
        $shop->setCreatorId(base64_decode($shopDTO->getCreator()));

        $shop->setSiretNumber($shopDTO->getSiretNumber());
        $shop->setTheme($shopDTO->getTheme());


        $entityManager->persist($shop);
        $entityManager->flush();

        return $this->json($shop, Response::HTTP_OK);
    }

    #[Route('/user/{creatorId}', name: 'creator_shop', methods: ['GET'])]
    public function shopByCreator(string $creatorId, ShopRepository $shopRepository, UserRepository $userRepository): JsonResponse
    {
        $email = base64_decode($creatorId);
        $user = $userRepository->findOneBy(['email' => $email]);
        $shop = $shopRepository->findOneBy(['creatorId' => $user->getEmail()]);

        if (!$shop) {
            return new JsonResponse(['error' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        $shopData = [
            'id' => $shop->getId(),
            'name' => $shop->getName(),
        ];

        return new JsonResponse($shopData, Response::HTTP_OK);
    }

    #[Route('/{id}/customers', name: 'shop_customers', methods: ['GET'])]
    public function getShopCustomers(int $id, UserRepository $userRepository, ShopRepository $shopRepository): JsonResponse
    {
        // Récupération de la boutique
        $shop = $shopRepository->find($id);

        if (!$shop) {
            return $this->json(['error' => 'Shop not found'], 404);
        }

        // Récupération des utilisateurs liés à la boutique via shopId
        $customers = $userRepository->findBy(['shopId' => $id]);

        foreach ($customers as $key => $customer) {
            $customer->setPassword('');
        }

        return $this->json($customers, 200);
    }
}

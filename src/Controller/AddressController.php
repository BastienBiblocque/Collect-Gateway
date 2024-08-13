<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\AddressRepository;
use App\Repository\ProductRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/private/{shopId}/{userId}/address')]
class AddressController extends AbstractController
{
    #[Route('/', name: 'address_index', methods: ['GET'])]
    public function index(string $shopId, string $userId, AddressRepository $addressRepository): JsonResponse
    {
        $address = $addressRepository->findBy(['userId' => $userId, 'shopId' => $shopId]);

        return $this->json($address, Response::HTTP_OK);
    }

    #[Route('/new', name: 'address_new', methods: ['POST'])]
    public function new(string $shopId, string $userId, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['delivery']['street'], $data['delivery']['city'], $data['delivery']['zipcode'])) {
            return new JsonResponse(['error' => 'Missing data'], Response::HTTP_BAD_REQUEST);
        }

        $deliveryAdress = new Address();
        $deliveryAdress->setStreet($data['delivery']['street']);
        $deliveryAdress->setCity($data['delivery']['city']);
        $deliveryAdress->setZipcode($data['delivery']['zipcode']);
        $deliveryAdress->setCountry($data['delivery']['country'] ?? 'France');
        $deliveryAdress->setUserId($userId);
        $deliveryAdress->setShopId($shopId);
        $entityManager->persist($deliveryAdress);

        if ($data['usebilling'] === false) {
            $address = new Address();
            $address->setStreet($data['billing']['street']);
            $address->setCity($data['billing']['city']);
            $address->setZipcode($data['billing']['zipcode']);
            $address->setCountry($data['billing']['country'] ?? 'France');
            $address->setUserId($userId);
            $address->setShopId($shopId);
            $entityManager->persist($address);
        }

        $entityManager->flush();

        $idToReturn = [
            'delivery' => $deliveryAdress->getId(),
            'billing' => $data['usebilling'] ? $deliveryAdress->getId() : $address->getId(),
        ];

        return $this->json($idToReturn, Response::HTTP_OK);
    }
}

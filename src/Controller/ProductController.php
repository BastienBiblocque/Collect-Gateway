<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/private/{shopId}/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product_index', methods: ['GET'])]
    public function index(int $shopId, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findBy(['shopId' => $shopId]);
        return $this->json($products, Response::HTTP_OK);
    }

    #[Route('/new', name: 'product_new', methods: ['POST'])]
    public function new(int $shopId, ShopRepository $shopRepository, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setQuantity($data['quantity']);
        $product->setDescription($data['description'] ?? null);
        $shop = $shopRepository->find($shopId);

        $product->setShopId($shop);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($product, Response::HTTP_OK);
    }

}

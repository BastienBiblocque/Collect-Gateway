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

#[Route('/api/public/{shopId}/product')]
class PublicProductController extends AbstractController
{
    #[Route('/', name: 'public.product_index', methods: ['GET'])]
    public function index(int $shopId, ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findBy(['shopId' => $shopId]);
        return $this->json($products, Response::HTTP_OK);
    }

    #[Route('/{productId}', name: 'public.get_product', methods: ['GET'])]
    public function getProductById(int $productId, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($product, Response::HTTP_OK);
    }
}

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
    public function index(int $shopId, ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findBy(['shopId' => $shopId]);
        return $this->json($products, Response::HTTP_OK);
    }

    #[Route('/{productId}', name: 'get_product', methods: ['GET'])]
    public function getProductById(int $productId, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($product, Response::HTTP_OK);
    }

    #[Route('/new', name: 'product_new', methods: ['POST'])]
    public function new(int $shopId, ShopRepository $shopRepository, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setQuantity($data['quantity']);
        $product->setDescription($data['description'] ?? 'description');
        $shop = $shopRepository->find($shopId);
        $product->setShopId($shop->getId());

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($product, Response::HTTP_OK);
    }

    #[Route('/{productId}', name: 'update_product', methods: ['PUT'])]
    public function updateProduct(Request $request, int $shopId, int $productId, ShopRepository $shopRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Trouver le shop par ID
        $shop = $shopRepository->find($shopId);

        if (!$shop) {
            return new JsonResponse(['error' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        $product = $productRepository->find($productId);

        if (!$product || $product->getShopId() !== $shop->getId()) {
            return new JsonResponse(['error' => 'Product not found or does not belong to the shop'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['quantity'])) {
            $product->setQuantity($data['quantity']);
        }

        try {
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->json($product, Response::HTTP_OK);

            return new JsonResponse(['message' => 'Product updated successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while updating the product'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{productId}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(int $shopId, int $productId, EntityManagerInterface $entityManager, ShopRepository $shopRepository, ProductRepository $productRepository): JsonResponse
    {
        // Trouver le shop par ID
        $shop = $shopRepository->find($shopId);

        if (!$shop) {
            return new JsonResponse(['error' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        $product = $productRepository->find($productId);

        if (!$product || $product->getShopId() !== $shop->getId()) {
            return new JsonResponse(['error' => 'Product not found or does not belong to the shop'], Response::HTTP_NOT_FOUND);
        }

        // Suppression du produit
        try {
            $entityManager->remove($product);
            $entityManager->flush();
            return new JsonResponse(['message' => 'Product deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while deleting the product'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        return $this->json($products);
    }

    #[Route('/new', name: 'product_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setQuantity($data['quantity']);
        $product->setDescription($data['description'] ?? null);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($product, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json($product);
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['PUT'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setDescription($data['description'] ?? null);

        $entityManager->flush();

        return $this->json($product);
    }

    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

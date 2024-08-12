<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\User;
use App\Repository\CartProductRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
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

#[Route('/api/private/{shopId}/{userId}/cart')]
class CartController extends AbstractController
{
    #[Route('/add/{productId}', name: 'cart_add', methods: ['POST'])]
    public function new(string $shopId, string $userId, string $productId, ProductRepository $productRepository, CartRepository $cartRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $productRepository->find($productId);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $cart = $cartRepository->findOneBy(['userId' => base64_decode($userId), 'shopId' => $shopId, 'status' => 'open']);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUserId(base64_decode($userId));
            $cart->setShopId($shopId);
            $cart->setStatus('open');
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        $cartProduct = new CartProduct();
        $cartProduct->setCartId($cart->getId());
        $cartProduct->setProductId($product->getId());
        $cartProduct->setName($product->getName());
        $cartProduct->setPrice($product->getPrice());

        $entityManager->persist($cartProduct);
        $entityManager->flush();

        return $this->json($cart, Response::HTTP_CREATED);
    }


    #[Route('/', name: 'cart_view', methods: ['GET'])]

    public function viewCart($userId, CartRepository $cartRepository, CartProductRepository $cartProductRepository, ProductRepository $productRepository): JsonResponse
    {
        $cart = $cartRepository->findOneBy(['userId' => base64_decode($userId), 'status' => 'open']);

        if (!$cart) {
            return new JsonResponse(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }

        $cartProducts = $cartProductRepository->findBy(['cartId' => $cart->getId()]);

        $cartDetails = [];
        foreach ($cartProducts as $cartProduct) {
            $cartDetails[] = $cartProduct;
        }

        $cart->setProducts($cartDetails);

        return $this->json($cart, Response::HTTP_CREATED);
    }

    #[Route('/{cartProductId}', name: 'delete_cart_product', methods: ['DELETE'])]
    public function deleteCartProduct(int $cartProductId, EntityManagerInterface $entityManager, CartProductRepository $cartProductRepository): JsonResponse
    {
        // Rechercher le produit du panier par son ID
        $cartProduct = $cartProductRepository->find($cartProductId);

        // Vérifier si le produit du panier existe
        if (!$cartProduct) {
            return new JsonResponse(['error' => 'Cart product not found'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer le produit du panier
        $entityManager->remove($cartProduct);
        $entityManager->flush();

        // Retourner une réponse JSON confirmant la suppression
        return new JsonResponse(['message' => 'Cart product deleted successfully'], Response::HTTP_OK);
    }
}

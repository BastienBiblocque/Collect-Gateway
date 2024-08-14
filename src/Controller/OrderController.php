<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\AddressRepository;
use App\Repository\CartProductRepository;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/private/{shopId}/order')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'order_byshop', methods: ['GET'])]
    public function getByShop(string $shopId, OrderRepository $orderRepository, CartRepository $cartRepository, CartProductRepository $cartProductRepository, AddressRepository $addressRepository, UserRepository $userRepository): JsonResponse
    {
        $orders = $orderRepository->findBy(['shopId' => $shopId]);

        foreach ($orders as $order) {
            $cart = $cartRepository->findOneBy(['id' => $order->getCartId()]);

            $cartProducts = $cartProductRepository->findBy(['cartId' => $cart->getId()]);

            $cartDetails = [];
            $total = 0;

            foreach ($cartProducts as $cartProduct) {
                $cartDetails[] = $cartProduct;
                $total += $cartProduct->getPrice();
            }

            $cart->setProducts($cartDetails);

            $cart->setTotalPrice($total);

            $order->setCart($cart);

            $user = $userRepository->findOneBy(['id' => $order->getUserId()]);
            $order->setUser($user);

            $order->setDeliveryAddress($addressRepository->find($order->getDevelieryId()));
            $order->setBillingAddress($addressRepository->find($order->getBillingId()));
        }

        return $this->json($orders, Response::HTTP_OK);
    }

    #[Route('/paid', name: 'order_paidbyshop', methods: ['GET'])]
    public function getPaidOrderByShop(string $shopId, OrderRepository $orderRepository, CartRepository $cartRepository, CartProductRepository $cartProductRepository, AddressRepository $addressRepository, UserRepository $userRepository): JsonResponse
    {
        $orders = $orderRepository->findBy(['shopId' => $shopId, 'status' => 'payed']);
        return $this->json($orders, Response::HTTP_OK);
    }

    #[Route('/{userId}', name: 'order_index', methods: ['GET'])]
    public function getByUser(string $shopId, string $userId, OrderRepository $orderRepository, CartRepository $cartRepository, CartProductRepository $cartProductRepository, AddressRepository $addressRepository): JsonResponse
    {
        $orders = $orderRepository->findBy(['shopId' => $shopId, 'userId' => base64_decode($userId)]);

        foreach ($orders as $order) {
            $cart = $cartRepository->findOneBy(['id' => $order->getCartId()]);

            $cartProducts = $cartProductRepository->findBy(['cartId' => $cart->getId()]);

            $cartDetails = [];
            $total = 0;

            foreach ($cartProducts as $cartProduct) {
                $cartDetails[] = $cartProduct;
                $total += $cartProduct->getPrice();
            }

            $cart->setProducts($cartDetails);

            $cart->setTotalPrice($total);

            $order->setCart($cart);

            $order->setDeliveryAddress($addressRepository->find($order->getDevelieryId()));
            $order->setBillingAddress($addressRepository->find($order->getBillingId()));
        }

        return $this->json($orders, Response::HTTP_OK);
    }



    #[Route('/{userId}/{orderId}', name: 'order_getOne', methods: ['GET'])]
    public function getOne(string $shopId, string $userId , string $orderId, OrderRepository $orderRepository, CartRepository $cartRepository, CartProductRepository $cartProductRepository, AddressRepository $addressRepository): JsonResponse
    {
        $order = $orderRepository->findOneBy(['shopId' => $shopId, 'userId' =>  base64_decode($userId), 'id' => $orderId]);

        $cart = $cartRepository->findOneBy(['id' => $order->getCartId()]);

        $cartProducts = $cartProductRepository->findBy(['cartId' => $cart->getId()]);

        $cartDetails = [];
        foreach ($cartProducts as $cartProduct) {
            $cartDetails[] = $cartProduct;
        }

        $cart->setProducts($cartDetails);

        $order->setCart($cart);

        $order->setDeliveryAddress($addressRepository->find($order->getDevelieryId()));
        $order->setBillingAddress($addressRepository->find($order->getBillingId()));

        return $this->json($order, Response::HTTP_OK);
    }

    #[Route('/{userId}/new', name: 'order_new', methods: ['POST'])]
    public function new(string $shopId, string $userId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $order = new Order();
        $order->setStatus('open');
        $order->setUserId(base64_decode($userId));
        $order->setShopId($shopId);
        $order->setCartId($data['cartId']);
        $order->setBillingId($data['billing']);
        $order->setDevelieryId($data['delivery']);
        $order->setCreatedAt(new \DateTime());
        $entityManager->persist($order);

        $entityManager->flush();

        return $this->json($order, Response::HTTP_OK);
    }

    #[Route('/{userId}/{orderId}/payed', name: 'order_pay', methods: ['PUT'])]
    public function payOrder(string $shopId, string $userId, string $orderId, OrderRepository $orderRepository, EntityManagerInterface $entityManager, CartRepository $cartRepository, CartProductRepository $cartProductRepository, ProductRepository $productRepository): JsonResponse
    {
        $order = $orderRepository->findOneBy(['userId' => base64_decode($userId), 'shopId' => $shopId, 'id' => $orderId]);
        $order->setStatus('payed');

        $cart = $cartRepository->findOneBy(['id' => $order->getCartId()]);
        $cartProducts = $cartProductRepository->findBy(['cartId' => $cart->getId()]);
        $orderArray = [];

        foreach ($cartProducts as $cartProduct) {
            $productId = $cartProduct->getProductId();
            if (array_key_exists($productId, $orderArray)) {
                $orderArray[$productId] += 1;
            } else {
                $orderArray[$productId] = 1;
            }
        }

        foreach ($orderArray as $productId => $count) {
            $product = $productRepository->findOneBy(['id' => $productId]);
            if(!$product) {
                return new JsonResponse(
                    ['error' => 'Product unavailable'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $quantityAvailable = $product->getQuantity();
            if($quantityAvailable<$count) {
                return new JsonResponse(
                    ['error' => 'Product unavailable in required quantity'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $product->setQuantity($product->getQuantity() - $count);
            $entityManager->persist($product);
        }

        $cart->setStatus('payed');

        $entityManager->persist($order);
        $entityManager->persist($cart);

        $entityManager->flush();

        return $this->json($order, Response::HTTP_OK);
    }

    #[Route('/send/{orderId}', name: 'order_send', methods: ['POST'])]
    public function sendOrder(string $shopId, string $orderId, OrderRepository $orderRepository, EntityManagerInterface $entityManager,): JsonResponse
    {
        $order = $orderRepository->findOneBy(['shopId' => $shopId, 'id' => $orderId]);
        $order->setStatus('send');

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json($order, Response::HTTP_OK);
    }
}

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
    public function getByShop(string $shopId, OrderRepository $orderRepository, CartRepository $cartRepository, CartProductRepository $cartProductRepository, AddressRepository $addressRepository): JsonResponse
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

            $order->setDeliveryAddress($addressRepository->find($order->getDevelieryId()));
            $order->setBillingAddress($addressRepository->find($order->getBillingId()));
        }

        return $this->json($orders, Response::HTTP_OK);
    }

    #[Route('/{userId}', name: 'order_index', methods: ['GET'])]
    public function getByUser(string $shopId, string $userId, OrderRepository $orderRepository, CartRepository $cartRepository, CartProductRepository $cartProductRepository, AddressRepository $addressRepository): JsonResponse
    {
        $orders = $orderRepository->findBy(['shopId' => $shopId, 'userId' => $userId]);

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
        $order = $orderRepository->findOneBy(['shopId' => $shopId, 'userId' => $userId, 'id' => $orderId]);

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
        $order->setUserId($userId);
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
    public function payOrder(string $shopId, string $userId, string $orderId, OrderRepository $orderRepository, EntityManagerInterface $entityManager, CartRepository $cartRepository): JsonResponse
    {
        $order = $orderRepository->findOneBy(['userId' => $userId, 'shopId' => $shopId, 'id' => $orderId]);
        $order->setStatus('payed');

        $cart = $cartRepository->findOneBy(['id' => $order->getCartId()]);
        $cart->setStatus('payed');

        $entityManager->persist($order);
        $entityManager->persist($cart);

        $entityManager->flush();

        return $this->json($order, Response::HTTP_OK);
    }
}

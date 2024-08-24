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
use App\service\ApiMicroservice\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/private/create-payment-intent')]
class StripeController extends AbstractController
{
    #[Route('/', name: 'stripe_create', methods: ['POST'])]
    public function getByShop(PaymentService $paymentService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $content = $paymentService->postRequest('/create-payment-intent', ['amount' => $data['amount']]);
        $decodedJson = json_decode($content, true);

        return $this->json($decodedJson, Response::HTTP_OK);
    }

}

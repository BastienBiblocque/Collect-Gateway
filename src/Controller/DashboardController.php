<?php

// src/Controller/ShopController.php

namespace App\Controller;

use App\DTO\ShopDTO;
use App\Entity\Shop;
use App\Repository\OrderRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\service\ApiMicroservice\DeploymentService;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/private/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/checkIfAdmin/{userId}', name: 'get_ifAdmin', methods: ['GET'])]
    public function checkIfAdmin(string $userId, ShopRepository $shopRepository): JsonResponse
    {
        $admin = ['hugo@gmail.com'];
        $isAdmin = false;

        $userId = base64_decode($userId);

        if (in_array($userId, $admin)) {
            $isAdmin = true;
        }

        return $this->json($isAdmin, Response::HTTP_OK);
    }

    #[Route('/shops/', name: 'getShops', methods: ['GET'])]
    public function getShops(ShopRepository $shopRepository, UserRepository $userRepository): JsonResponse
    {
        $shops = $shopRepository->findAll();

        foreach ($shops as $shop) {
            $user = $userRepository->find($shop->getCreatorId());
            if ($user) {
                $user->setPassword('');
                $shop->setOwner($user);
            }
        }

        return $this->json($shops, Response::HTTP_OK);
    }
}

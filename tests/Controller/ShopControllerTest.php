<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Shop;

class ShopControllerTest extends WebTestCase
{
    private static $client = null;
    private static $entityManager = null;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
        self::$entityManager = self::$client->getContainer()->get('doctrine')->getManager();
    }

    private function getAuthToken(): string
    {
        self::$client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user@example.com',
                'password' => 'password',
            ])
        );

        $response = self::$client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Login request failed: ' . $response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);

        return $data['token'];
    }

    private function getAuthHeaders(): array
    {
        $token = $this->getAuthToken();
        return [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    private function createTestUser(): int
    {
        $email = 'testuser' . uniqid() . '@example.com'; // Assurez-vous que l'email est unique
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('password');
        // Ajouter d'autres propriétés requises pour l'utilisateur

        self::$entityManager->persist($user);
        self::$entityManager->flush();

        return $user->getId();
    }

    public function testGetAllShops(): void
    {
        // Assurez-vous qu'il y a des boutiques existantes pour ce test
        self::$client->request('GET', '/api/private/shop/', [], [], $this->getAuthHeaders());

        $response = self::$client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to get all shops: ' . $response->getContent());
        $this->assertJson($response->getContent());

        $shops = json_decode($response->getContent(), true);
        $this->assertIsArray($shops);
    }
//TODO Fix Commented Test

//    public function testCreateShop(): void
//    {
//        $userId = $this->createTestUser();
//
//        $data = [
//            'name' => 'New Shop',
//            'creator' => $userId, // Utilisez l'ID de l'utilisateur comme créateur
//        ];
//
//        self::$client->request(
//            'POST',
//            '/api/private/shop/new',
//            [],
//            [],
//            $this->getAuthHeaders(),
//            json_encode($data)
//        );
//
//        $response = self::$client->getResponse();
//        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to create shop: ' . $response->getContent());
//        $this->assertJson($response->getContent());
//
//        $shop = json_decode($response->getContent(), true);
//        $this->assertArrayHasKey('id', $shop);
//    }
//
//    public function testCreateShopConflict(): void
//    {
//        $userId = $this->createTestUser();
//
//        $shopName = 'Existing Shop Name'; // Assurez-vous que ce nom existe déjà dans la base de données
//        $data = [
//            'name' => $shopName,
//            'creator' => $userId,
//        ];
//
//        self::$client->request(
//            'POST',
//            '/api/private/shop/new',
//            [],
//            [],
//            $this->getAuthHeaders(),
//            json_encode($data)
//        );
//
//        $response = self::$client->getResponse();
//        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode(), 'Expected conflict but got: ' . $response->getContent());
//        $this->assertJson($response->getContent());
//
//        $errorResponse = json_decode($response->getContent(), true);
//        $this->assertArrayHasKey('error', $errorResponse);
//    }
//
//    public function testShopByCreator(): void
//    {
//        $userId = $this->createTestUser();
//        $shop = new Shop();
//        $shop->setName('Test Shop');
//        $shop->setCreatorId($userId);
//        self::$entityManager->persist($shop);
//        self::$entityManager->flush();
//
//        $creatorEmail = base64_encode('testuser' . uniqid() . '@example.com');
//        self::$client->request('GET', '/api/private/shop/user/' . $creatorEmail, [], [], $this->getAuthHeaders());
//
//        $response = self::$client->getResponse();
//        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to get shop by creator: ' . $response->getContent());
//        $this->assertJson($response->getContent());
//
//        $shopData = json_decode($response->getContent(), true);
//        $this->assertArrayHasKey('id', $shopData);
//    }
//
//    public function testGetShopCustomers(): void
//    {
//        $userId = $this->createTestUser();
//        $shop = new Shop();
//        $shop->setName('Test Shop');
//        $shop->setCreatorId($userId);
//        self::$entityManager->persist($shop);
//        self::$entityManager->flush();
//
//        $customer = new User();
//        $customer->setEmail('customer' . uniqid() . '@example.com');
//        $customer->setPassword('password');
//        $customer->setShopId($shop->getId());
//        self::$entityManager->persist($customer);
//        self::$entityManager->flush();
//
//        self::$client->request('GET', '/api/private/shop/' . $shop->getId() . '/customers', [], [], $this->getAuthHeaders());
//
//        $response = self::$client->getResponse();
//        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to get shop customers: ' . $response->getContent());
//        $this->assertJson($response->getContent());
//
//        $customers = json_decode($response->getContent(), true);
//        $this->assertIsArray($customers);
//        $this->assertNotEmpty($customers);
//    }
}

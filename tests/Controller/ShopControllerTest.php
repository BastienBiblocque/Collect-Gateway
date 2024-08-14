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
    public function testCreateShop(): void
    {
        $userId = $this->createTestUser();

        $data = [
            'name' => 'New Shop',
            'creator' => $userId,
        ];

        self::$client->request(
            'POST',
            '/api/private/shop/new',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($data)
        );

        $response = self::$client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to create shop: ' . $response->getContent());
        $this->assertJson($response->getContent());

        $shop = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $shop);
        $shopId = $shop['id'];

        $entityManager = self::$client->getContainer()->get('doctrine')->getManager();
        $shopToDelete = $entityManager->getRepository(Shop::class)->find($shopId);

        if ($shopToDelete) {
            $entityManager->remove($shopToDelete);
            $entityManager->flush();
        }
    }

    public function testCreateShopConflict(): void
    {
        // Créer un utilisateur de test
        $userId = $this->createTestUser();

        // Nom de la boutique à créer (et à utiliser pour provoquer le conflit)
        $shopName = 'Conflicting Shop Name';

        // Créer la première boutique avec ce nom
        $initialData = [
            'name' => $shopName,
            'creator' => $userId,
        ];

        self::$client->request(
            'POST',
            '/api/private/shop/new',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($initialData)
        );

        $initialResponse = self::$client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $initialResponse->getStatusCode(), 'Failed to create initial shop: ' . $initialResponse->getContent());
        $this->assertJson($initialResponse->getContent());

        // Décoder la réponse pour obtenir l'ID de la boutique initiale
        $initialShop = json_decode($initialResponse->getContent(), true);
        $this->assertArrayHasKey('id', $initialShop);
        $shopId = $initialShop['id'];

        // Tenter de créer une autre boutique avec le même nom pour provoquer un conflit
        self::$client->request(
            'POST',
            '/api/private/shop/new',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($initialData)
        );

        $response = self::$client->getResponse();
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode(), 'Expected conflict but got: ' . $response->getContent());
        $this->assertJson($response->getContent());

        $errorResponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $errorResponse);

        // Supprimer la boutique initiale après le test
        $entityManager = self::$client->getContainer()->get('doctrine')->getManager();
        $shopToDelete = $entityManager->getRepository(Shop::class)->find($shopId);

        if ($shopToDelete) {
            $entityManager->remove($shopToDelete);
            $entityManager->flush();
        }
    }

    public function testShopByCreator(): void
    {
        // Créer un utilisateur de test
        $userId = $this->createTestUser();

        // Récupérer l'utilisateur pour obtenir son email
        $user = self::$entityManager->getRepository(User::class)->find($userId);
        $this->assertNotNull($user, 'Test user not found');

        // Générer un numéro SIRET valide (14 chiffres)
        $siretNumber = str_pad((string)random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);

        // Créer une boutique pour cet utilisateur avec le numéro SIRET
        $shop = new Shop();
        $shop->setName('Test Shop');
        $shop->setCreatorId($userId);
        $shop->setSiretNumber($siretNumber);
        self::$entityManager->persist($shop);
        self::$entityManager->flush();

        // Encoder l'email de l'utilisateur en base64
        $creatorEmail = base64_encode($user->getEmail());

        // Faire une requête GET pour obtenir la boutique par le créateur (l'utilisateur)
        self::$client->request('GET', '/api/private/shop/user/' . $creatorEmail, [], [], $this->getAuthHeaders());

        // Vérifier que la réponse a le statut HTTP 200 OK
        $response = self::$client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to get shop by creator: ' . $response->getContent());
        $this->assertJson($response->getContent());

        // Vérifier que la réponse contient l'ID de la boutique
        $shopData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $shopData);

        // Nettoyer en supprimant la boutique créée
        self::$entityManager->remove($shop);
        self::$entityManager->flush();
    }

    public function testGetShopCustomers(): void
    {
        // Créer un utilisateur de test
        $userId = $this->createTestUser();

        // Générer un numéro SIRET valide (14 chiffres)
        $siretNumber = str_pad((string)random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);

        // Créer une boutique pour cet utilisateur avec le numéro SIRET
        $shop = new Shop();
        $shop->setName('Test Shop');
        $shop->setCreatorId($userId);
        $shop->setSiretNumber($siretNumber);
        self::$entityManager->persist($shop);
        self::$entityManager->flush();

        // Créer un client (utilisateur) associé à la boutique
        $customer = $this->createTestUser();
        $customerUser = self::$entityManager->getRepository(User::class)->find($customer);
        $customerUser->setShopId($shop->getId());
        self::$entityManager->persist($customerUser);
        self::$entityManager->flush();

        // Faire une requête GET pour obtenir les clients de la boutique
        self::$client->request('GET', '/api/private/shop/' . $shop->getId() . '/customers', [], [], $this->getAuthHeaders());

        // Vérifier que la réponse a le statut HTTP 200 OK
        $response = self::$client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Failed to get shop customers: ' . $response->getContent());
        $this->assertJson($response->getContent());

        // Vérifier que la réponse contient les clients
        $customersData = json_decode($response->getContent(), true);
        $this->assertIsArray($customersData, 'Expected customers array');
        $this->assertCount(1, $customersData, 'Expected 1 customer');
        $this->assertEquals('', $customersData[0]['password'], 'Password should be empty');

        // Nettoyer en supprimant la boutique et les utilisateurs créés
        self::$entityManager->remove($customerUser);
        self::$entityManager->remove($shop);
        self::$entityManager->flush();
    }

    public function testGetShopCustomersShopNotFound(): void
    {
        // ID invalide pour la boutique
        $invalidShopId = 999999;

        // Faire une requête GET pour obtenir les clients d'une boutique inexistante
        self::$client->request('GET', '/api/private/shop/' . $invalidShopId . '/customers', [], [], $this->getAuthHeaders());

        // Vérifier que la réponse a le statut HTTP 404 NOT FOUND
        $response = self::$client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode(), 'Expected 404 for non-existing shop');
        $this->assertJson($response->getContent());

        // Vérifier que la réponse contient l'erreur
        $errorResponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $errorResponse);
        $this->assertEquals('Shop not found', $errorResponse['error']);
    }
}

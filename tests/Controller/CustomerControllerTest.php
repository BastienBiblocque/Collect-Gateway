<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerControllerTest extends WebTestCase
{
    public function testNewCustomer()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        // Création du nouvel utilisateur customer
        $client->request('POST', '/api/public/1/customer/new', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'customer@example.com',
            'password' => 'customerpassword',
            'firstname' => 'customerfirstname',
            'lastname' => 'customerlastname'
        ]));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        // Suppression de l'utilisateur après le test
        $user = $userRepository->findOneBy(['email' => 'customer@example.com']);
        if ($user) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
    }
}
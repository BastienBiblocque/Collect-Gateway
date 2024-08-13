<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testNewUser()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        // Création du nouvel utilisateur
        $client->request('POST', '/api/user/new', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@example.com',
            'password' => 'newpassword',
            'firstname' => 'newuserfirstname',
            'lastname' => 'newuserlastname'
        ]));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        // Suppression de l'utilisateur après le test
        $user = $userRepository->findOneBy(['email' => 'newuser@example.com']);
        if ($user) {
            $entityManager->remove($user);
            $entityManager->flush();
        }
    }
}
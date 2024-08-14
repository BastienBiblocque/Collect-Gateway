<?php

namespace App\service\ApiMicroservice;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class DeploymentService
{
    private $client;
    private $microserviceUrl;

    public function __construct()
    {
        // Initialiser le client Guzzle
        $this->client = new Client();
        // Récupérer l'URL du microservice depuis les variables d'environnement
        $this->microserviceUrl = $_ENV['URL_MICROSERVICE_DEPLOYMENT'];
    }

    public function postRequest(string $endpoint, array $entities)
    {
        try {
            $client = new Client();
            $response = $client->post('http://127.0.0.1:8001' . $endpoint, [
                'json' => $entities, // Utilisation des données du shop
            ]);

            $body = $response->getBody();
            return $body->getContents();
        } catch (RequestException $e) {
            // Gérer les erreurs de la requête
            return 'Request failed: ' . $e->getMessage();
        }
    }
}

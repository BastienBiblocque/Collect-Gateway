<?php

namespace App\service\ApiMicroservice;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PaymentService
{
    private $client;
    private $microserviceUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->microserviceUrl = $_ENV['URL_MICROSERVICE_PAYMENT'];
    }

    public function postRequest(string $endpoint, array $entities)
    {
        try {
            $client = new Client();
            $response = $client->post('http://127.0.0.1:8002' . $endpoint, [
                'json' => $entities,
            ]);

            $body = $response->getBody();
            return $body->getContents();
        } catch (RequestException $e) {
            // Gérer les erreurs de la requête
            return 'Request failed: ' . $e->getMessage();
        }
    }
}

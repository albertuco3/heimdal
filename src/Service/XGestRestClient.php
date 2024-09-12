<?php


namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class XGestRestClient
{
    private HttpClientInterface $client;
    private array $http_client_options;

    public function __construct(string $baseUrl, string $apiKey)
    {

        $this->client = HttpClient::createForBaseUri($baseUrl, [
            // Opciones para desactivar la verificación SSL en este ejemplo, pero se recomienda manejar correctamente la validación de certificados en producción
            'verify_peer' => false,
            'verify_host' => false,
        ]);


        $this->http_client_options = [
            'headers' => [
                'X-ApiKey' => $apiKey,
            ],
        ];

    }

    public function getDeliveryNote(string $number, string $company): ?array
    {

        $response = $this->client->request('GET', 'delivery-notes/'.$number, array_merge($this->http_client_options, [
            'query' => [
                'company' => $company,
            ]
        ]));

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getDeliveryNotes(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null, ?string $company = null): ?array
    {

        $response = $this->client->request('GET', 'delivery-notes', array_merge($this->http_client_options, [
            'query' => array_filter([
                'from' => $from?->format('Y-m-d'),
                'to' => $to?->format('Y-m-d'),
                'company' => $company,
            ])
        ]));

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getStock(array $warehouses = [], ?string $company = null)
    {
        $response = $this->client->request('GET', 'stock', array_merge($this->http_client_options, [
            'query' => array_filter([
                'warehouses' => implode(",", array_filter(array_values($warehouses))),
                'company' => $company
            ])
        ]));

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }


}
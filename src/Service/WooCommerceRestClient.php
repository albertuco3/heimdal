<?php


namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WooCommerceRestClient
{
    private HttpClientInterface $client;
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;

    public function __construct(string $baseUrl, string $consumerKey, string $consumerSecret)
    {
        $this->baseUrl = $baseUrl;
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->client = HttpClient::create([
            'base_uri' => $this->baseUrl,
            'auth_basic' => [$this->consumerKey, $this->consumerSecret]
        ]);
    }

    public function put(string $endpoint, array $data): array
    {
        $response = $this->client->request('PUT', $endpoint, [
            'json' => $data
        ]);

        return $this->processResponse($response);
    }

    public function delete(string $endpoint, array $parameters = []): array
    {
        $response = $this->client->request('DELETE', $endpoint, [
            'query' => $parameters
        ]);

        return $this->processResponse($response);
    }

    public function get(string $endpoint, array $parameters = []): array
    {
        $response = $this->client->request('GET', $endpoint, [
            'query' => $parameters
        ]);

        return $this->processResponse($response);
    }

    public function post(string $endpoint, array $data): array
    {
        $response = $this->client->request('POST', $endpoint, [
            'json' => $data
        ]);


        return $this->processResponse($response);
    }

    private function processResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            return $response->toArray();
        } else {
            throw new \Exception("API call failed with status code {$statusCode}: " . $response->getContent(false));
        }
    }

    public function getOrders(array $parameters = []): array
    {
        return $this->get('wc/v3/orders', $parameters);
    }

    public function getCustomers(array $parameters = []): array
    {
        return $this->get('wc/v3/customers', $parameters);
    }

    public function getProducts(array $parameters = []): array
    {
        return $this->get('wc/v3/products', $parameters);
    }

    public function getProductVariations(int $productId, array $parameters = []): array
    {
        return $this->get("wc/v3/products/{$productId}/variations", $parameters);
    }
}
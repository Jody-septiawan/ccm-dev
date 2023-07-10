<?php

namespace App\Services\ExternalAPIs;

use GuzzleHttp\Client;
use App\Libs\Json\JsonResponse;

class CrmAPI {
    public $client;
    public $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('API_URL_CRM_GABUNGIN') . '/api/v2/';
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    public function get(string $url, array $params = [])
    {
        try {
            $response = $this->client->request('GET', $url, [
                'query' => $params
            ]);
    
            return json_decode($response->getBody()->getContents());
        } catch (RequestException $th) {
            return $e->hasResponse();
        }
    }

    public function create(string $url, array $data)
    {
        $response = $this->client->request('POST', $url, [
            'json' => $data
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function update(string $url, array $data)
    {
        $response = $this->client->request('PUT', $url, [
            'json' => $data
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function delete(string $url)
    {
        $response = $this->client->request('DELETE', $url);

        return $response->getStatusCode() === 204;
    }
}

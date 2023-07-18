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

    /**
     * Get data from API
     *
     * @param string $url
     * @param array $params
     * @param string $token
     * 
     * @return void
     */
    public function get(string $url, array $params = [], string $token = null)
    {
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer $token"
                ],
                'query' => $params
            ]);
    
            return json_decode($response->getBody()->getContents());
        } catch (RequestException $th) {
            return $e->hasResponse();
        }
    }

    /**
     * Create data from API
     *
     * @param string $url
     * @param array $data
     * @param string $token
     * 
     * @return void
     */
    public function create(string $url, array $data, string $token = null, array $headers = [])
    {
        $authorization = [
            'Authorization' => "Bearer $token",
        ];

        $headers = array_merge($authorization, $headers);
        
        $response = $this->client->request('POST', $url, [
            'headers' => $headers,
            'json' => $data
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Update data from API with PUT method
     *
     * @param string $url
     * @param array $data
     * @param string $token
     * 
     * @return void
     */
    public function put(string $url, array $data, string $token = null)
    {
        $response = $this->client->request('PUT', $url, [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'json' => $data
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Update data from API with PATCH method
     *
     * @param string $url
     * @param array $data
     * @param string $token
     * 
     * @return void
     */
    public function patch(string $url, array $data, string $token = null)
    {
        $response = $this->client->request('PATCH', $url, [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'json' => $data
        ]);
    
        return json_decode($response->getBody()->getContents());
    }
    

    /**
     * Delete data from API
     *
     * @param string $url
     * @param string $token
     * 
     * @return void
     */
    public function delete(string $url, string $token = null)
    {
        $response = $this->client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
        ]);

        return $response->getStatusCode() === 204;
    }
}

<?php
namespace App\API;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ApiClient
{
    private \GuzzleHttp\Client $httpClient;
    private LoggerInterface $logger;
    private string|array|false $apiUrl;
    private string|array|false $username;
    private string|array|false $password;

    public function __construct(\GuzzleHttp\Client $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiUrl = getenv('API_URL');
        $this->username = getenv('API_USERNAME');
        $this->password = getenv('API_PASSWORD');
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function sendRequest($method, $params)
    {
        try {
            $this->logger->info('Sending request', [
                'method' => $method,
                'params' => $params
            ]);

            $response = $this->httpClient->post($this->apiUrl . '/json-rpc', [
                'json' => [
                    'jsonrpc' => '2.0',
                    'method' => $method,
                    'params' => $params,
                    'id' => 1
                ],
                'auth' => [$this->username, $this->password]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($responseData === null) {
                throw new \Exception('API response is null');
            }

            $this->logger->info('Received response', [
                'response' => $responseData
            ]);

            return $responseData;

        } catch (\Exception $e) {
            $this->logger->error('Request failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

}

<?php
namespace App\API;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class TokenService
{
    private ApiClient $apiClient;
    private LoggerInterface $logger;

    public function __construct(ApiClient $apiClient, LoggerInterface $logger)
    {
        $this->apiClient = $apiClient;
        $this->logger = $logger;
    }

    public function getToken()
    {
        try {
            $response = $this->apiClient->sendRequest('getToken', [
                'username' => getenv('API_USERNAME'),
                'password' => getenv('API_PASSWORD')
            ]);

            if (isset($response['result']['token'])) {
                return $response['result']['token'];
            }

            throw new \Exception('Unable to retrieve token');
        } catch (\Exception $e) {
            $this->logger->error('Token retrieval failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Unable to retrieve token');
        } catch (GuzzleException $e) {
            $this->logger->error('Guzzle exception during token retrieval', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Unable to retrieve token due to network issue');
        }
    }
}

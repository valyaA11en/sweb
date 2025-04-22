<?php
namespace App\API;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\GuzzleException;

class DomainService
{
    private ApiClient $apiClient;
    private LoggerInterface $logger;
    private TokenService $tokenService;

    public function __construct(ApiClient $apiClient, LoggerInterface $logger, TokenService $tokenService)
    {
        $this->apiClient = $apiClient;
        $this->logger = $logger;
        $this->tokenService = $tokenService;
    }

    public function addDomain($domain): array
    {
        try {
            $token = $this->tokenService->getToken();

            if (!$token) {
                throw new \Exception('Unable to retrieve token');
            }

            $response = $this->apiClient->sendRequest('addDomain', ['domain' => $domain]);

            if ($response === null) {
                throw new \Exception('API returned null response');
            }

            if (isset($response['error']) && $response['error'] === 'domain_exists') {
                throw new \Exception('The domain already exists on the account.');
            }

            return [
                'success' => true,
                'domain' => $domain
            ];

        } catch (\Exception $e) {
            $this->logger->error('Domain addition failed', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (GuzzleException $e) {
            $this->logger->error('Guzzle request failed', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Guzzle request failed');
        }
    }


}

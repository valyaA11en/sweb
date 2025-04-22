<?php
namespace App\API;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\GuzzleException;

class DomainService
{
    const string ERROR_DOMAIN_EXISTS = 'ОШИБКА ДОМЕНА СУЩЕСТВУЕТ';
    private ApiClient $apiClient;
    private LoggerInterface $logger;
    private TokenService $tokenService;

    public function __construct(ApiClient $apiClient, LoggerInterface $logger, TokenService $tokenService)
    {
        $this->apiClient = $apiClient;
        $this->logger = $logger;
        $this->tokenService = $tokenService;
    }

    public function addDomain(string $domain): array
    {
        $requestId = uniqid('domain_add_', true); // Генерация уникального ID для каждого запроса

        try {
            $token = $this->tokenService->getToken();

            if (!$token) {
                throw new \Exception('Unable to retrieve token');
            }

            $this->logger->info("Начинаем добавление домена [$requestId]", [
                'domain' => $domain,
                'request_id' => $requestId
            ]);

            $response = $this->apiClient->sendRequest('addDomain', ['domain' => $domain]);

            // Обновленная проверка
            if ($response === null || $response === false) {
                throw new \Exception('API returned null or false response');
            }

            // Использование констант для ошибок
            if (isset($response['error']) && $response['error'] === DomainService::ERROR_DOMAIN_EXISTS) {
                throw new \Exception('The domain already exists on the account.');
            }

            // Логирование успешного добавления домена после успешного завершения
            $this->logger->info("Домен [$domain] успешно добавлен", [
                'request_id' => $requestId
            ]);

            return [
                'success' => true,
                'domain' => $domain
            ];

        } catch (\Exception $e) {
            $this->logger->error("Ошибка при добавлении домена [$requestId]", [
                'domain' => $domain,
                'error' => $e->getMessage(),
                'request_id' => $requestId
            ]);
            throw $e;
        } catch (GuzzleException $e) {
            $this->logger->error("Guzzle ошибка при добавлении домена [$requestId]", [
                'domain' => $domain,
                'error' => $e->getMessage(),
                'request_id' => $requestId
            ]);
            throw new \Exception('Guzzle request failed');
        }
    }

}

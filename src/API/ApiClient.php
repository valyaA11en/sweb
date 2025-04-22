<?php
namespace App\API;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class ApiClient
{
    private HttpClient $httpClient;
    private LoggerInterface $logger;
    private string $baseUri;
    private ?TokenService $tokenService = null;

    public function __construct(HttpClient $httpClient, LoggerInterface $logger, string $baseUri, ?TokenService $tokenService = null)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->baseUri = $baseUri;
        $this->tokenService = $tokenService;
    }

    public function sendRequest(string $method, array $params = []): bool
    {
        $requestId = Uuid::uuid4()->toString();

        try {
            $params['jsonrpc'] = '2.0';
            if ($this->tokenService !== null) {
                $token = $this->tokenService->getToken();
                if (!$token) {
                    throw new \Exception('Unable to retrieve token');
                }
                $params['token'] = $token;
            }

            $this->logger->info("Отправка запроса [$requestId] на $method с параметрами:", $params);

            $response = $this->httpClient->request('POST', $this->baseUri . '/' . $method, [
                'json' => $params,
                'verify' => false,
                'stream' => true
            ]);

            $body = $response->getBody();
            $content = '';
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $content .= $chunk;
                $this->processDataChunk($chunk, $requestId);
            }

            $decoded = json_decode($content, true);
            if (!isset($decoded['result'])) {
                throw new \Exception('API returned null response');
            }

            $this->logger->info("Запрос [$requestId] завершён успешно с кодом ответа: " . $response->getStatusCode());

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $this->logger->error("Ошибка при отправке запроса [$requestId] на $method", [
                'error' => $e->getMessage(),
                'request_id' => $requestId
            ]);
            throw $e;
        } catch (GuzzleException $e) {
            $this->logger->error("Guzzle ошибка при отправке запроса [$requestId] на $method", [
                'method' => $method,
                'params' => array_slice($params, 0, 5),
                'request_id' => $requestId
            ]);
            throw $e;
        }
    }


    private function processDataChunk($chunk, string $requestId): void
    {
        $data = json_decode($chunk, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error("Ошибка при декодировании JSON для запроса [$requestId]: " . json_last_error_msg(), [
                'chunk' => substr($chunk, 0, 500),
                'request_id' => $requestId
            ]);
            return;
        }

        $this->logger->info("Получен чанк данных для запроса [$requestId]", [
            'data' => array_slice($data, 0, 3),
            'request_id' => $requestId
        ]);
    }

    public function setTokenService(TokenService $tokenService): void
    {
        $this->tokenService = $tokenService;
    }
}

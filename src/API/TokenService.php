<?php
namespace App\API;

use AllowDynamicProperties;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

#[AllowDynamicProperties]
class TokenService
{
    private ApiClient $apiClient;
    private LoggerInterface $logger;
    private ?string $token = null;
    private string $username;
    private string $password;

    public function __construct(ApiClient $apiClient, LoggerInterface $logger, string $username, string $password)
    {
        $this->apiClient = $apiClient;
        $this->logger = $logger;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Получить токен, если он уже существует, или запросить новый
     *
     * @throws \Exception
     * @throws GuzzleException
     */
    public function getToken(): string
    {
        if ($this->token) {
            return $this->token;
        }

        $response = $this->apiClient->sendRequest('getToken', ['username' => $this->username, 'password' => $this->password]);

        if (isset($response['result']['token'])) {
            $this->token = $response['result']['token'];
            return $this->token;
        }

        return $this->fetchNewToken();
    }

    /**
     * Получить новый токен с сервера
     *
     * @throws \Exception
     */
    public function fetchNewToken(): string
    {
        try {
            $response = $this->apiClient->sendRequest('getToken', [
                'username' => $this->username,
                'password' => $this->password
            ]);

            if (isset($response['result']['token'])) {
                $this->token = $response['result']['token'];

                // Логирование успешного получения токена
                $this->logger->info('Token successfully retrieved', [
                    'username' => $this->username,
                    'response' => 'Token received'
                ]);

                return $this->token;
            }

            throw new \Exception('Token not found in response: ' . json_encode($response));
        } catch (\Exception $e) {
            $this->logger->error('Token retrieval failed', [
                'error' => $e->getMessage(),
                'username' => $this->username
            ]);
            throw new \Exception('Unable to retrieve token: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            $this->logger->error('Guzzle exception during token retrieval', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'username' => $this->username
            ]);
            throw new \Exception('Unable to retrieve token due to network issue');
        }
    }

}

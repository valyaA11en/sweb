<?php
use PHPUnit\Framework\TestCase;
use App\API\ApiClient;
use App\API\TokenService;
use Psr\Log\LoggerInterface;

class TokenServiceTest extends TestCase
{
    /**
     * Тест для возврата кэшированного токена
     * @throws Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testGetTokenReturnsCachedToken()
    {
        $mockApiClient = $this->createMock(ApiClient::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        $username = getenv('API_USERNAME') ?: 'mock-username';
        $password = getenv('API_PASSWORD') ?: 'mock-password';

        $tokenService = new TokenService($mockApiClient, $mockLogger, $username, $password);

        $reflection = new \ReflectionClass($tokenService);
        $property = $reflection->getProperty('token');
        $property->setValue($tokenService, 'cached-token');

        $token = $tokenService->getToken();

        $this->assertEquals('cached-token', $token);

        $mockApiClient->expects($this->never())
            ->method('sendRequest');
    }

}

<?php
use PHPUnit\Framework\TestCase;
use App\API\ApiClient;
use App\API\TokenService;
use Psr\Log\LoggerInterface;

class TokenServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetToken()
    {
        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->method('sendRequest')->willReturn([
            'result' => ['token' => 'sample-token']
        ]);

        $logger = $this->createMock(LoggerInterface::class);

        $tokenService = new TokenService($apiClient, $logger);

        $token = $tokenService->getToken();

        $this->assertEquals('sample-token', $token);
    }
}

<?php

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as HttpClient;
use App\API\ApiClient;
use App\API\TokenService;
use App\API\DomainService;
use App\Config\Config;

class DomainServiceTest extends TestCase
{
    private $mockApiClient;
    private $mockTokenService;
    private $mockLogger;
    private $domainService;

    protected function setUp(): void
    {
        // Мокаем зависимости
        $this->mockApiClient = $this->createMock(ApiClient::class);
        $this->mockTokenService = $this->createMock(TokenService::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        // Создаем экземпляр DomainService с мокаемыми зависимостями
        $this->domainService = new DomainService($this->mockApiClient, $this->mockLogger, $this->mockTokenService);
    }

    /**
     * @throws Exception
     */
    public function testAddDomainSuccess()
    {
        // Мокаем ответ от TokenService
        $this->mockTokenService->method('getToken')->willReturn('mock_token');

        // Мокаем ответ от ApiClient (предположим, что запрос к API прошел успешно)
        $this->mockApiClient->method('sendRequest')->willReturn(true);  // Mocking a successful boolean response

        // Мокаем логгер, чтобы проверить, что он был вызван
        $this->mockLogger->expects($this->exactly(2))
            ->method('info')
            ->with(
                $this->logicalOr(
                    $this->stringContains("Начинаем добавление домена"),
                    $this->stringContains("Домен [test-domain.ru] успешно добавлен")
                )
            );

        $result = $this->domainService->addDomain('test-domain.ru');

        $this->assertEquals([
            'success' => true,
            'domain' => 'test-domain.ru'
        ], $result);
    }

    public function testAddDomainApiFailure()
    {
        $this->mockTokenService->method('getToken')->willReturn('mock_token');

        $this->mockApiClient->method('sendRequest')->willReturn(false);

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with($this->stringContains("Ошибка при добавлении домена"));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API returned null or false response');

        $this->domainService->addDomain('test-domain.ru');
    }
}

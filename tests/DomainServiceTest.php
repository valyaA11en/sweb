<?php
use PHPUnit\Framework\TestCase;
use App\API\ApiClient;
use App\API\TokenService;
use App\API\DomainService;
use Psr\Log\LoggerInterface;

class DomainServiceTest extends TestCase
{
    private $apiClient;
    private $tokenService;
    private $logger;
    private $domainService;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tokenService = new TokenService($this->apiClient, $this->logger);
        $this->domainService = new DomainService($this->apiClient, $this->logger, $this->tokenService);
    }

    /**
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAddDomainSuccess()
    {
        $apiClient = $this->createMock(ApiClient::class);
        $logger = $this->createMock(LoggerInterface::class);
        $tokenService = $this->createMock(TokenService::class);

        $tokenService->method('getToken')->willReturn('mocked_token');

        $apiClient->method('sendRequest')->willReturn(['success' => true]);

        $domainService = new DomainService($apiClient, $logger, $tokenService);

        $response = $domainService->addDomain('example.com');

        $this->assertIsArray($response);
        $this->assertEquals(['success' => true, 'domain' => 'example.com'], $response);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addDomain($domain)
    {
        $response = $this->apiClient->sendRequest('addDomain', ['domain' => $domain]);

        if (isset($response['error']) && $response['error']['message'] === 'Domain already exists') {
            throw new \Exception('The domain already exists on the account.');
        }

        return $response;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAddDomainAlreadyExists()
    {
        $apiClient = $this->createMock(ApiClient::class);
        $logger = $this->createMock(LoggerInterface::class);
        $tokenService = $this->createMock(TokenService::class);

        $tokenService->method('getToken')->willReturn('mocked_token');

        $apiClient->method('sendRequest')->willReturn(['error' => 'domain_exists']);

        $domainService = new DomainService($apiClient, $logger, $tokenService);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The domain already exists on the account.');

        $domainService->addDomain('existing.ru');
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAddDomainError()
    {
        $apiClient = $this->createMock(ApiClient::class);
        $logger = $this->createMock(LoggerInterface::class);
        $tokenService = $this->createMock(TokenService::class);

        $tokenService->method('getToken')->willThrowException(new \Exception('Unable to retrieve token'));

        $domainService = new DomainService($apiClient, $logger, $tokenService);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to retrieve token');

        $domainService->addDomain('example.com');
    }

}

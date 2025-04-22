<?php

use App\API\TokenService;
use App\API\ApiClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class ApiClientTest extends TestCase
{
    private $httpClient;
    private $logger;
    private $tokenService;
    private $apiClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tokenService = $this->createMock(TokenService::class);

        $this->apiClient = new ApiClient($this->httpClient, $this->logger, 'https://api.sweb.ru', $this->tokenService);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSendRequestSuccess()
    {
        $this->tokenService->method('getToken')->willReturn('sample-token');

        $bodyStream = Utils::streamFor(json_encode(['result' => 'success']));
        $mockResponse = new Response(200, [], $bodyStream);

        $this->httpClient->method('request')->willReturn($mockResponse);

        $result = $this->apiClient->sendRequest('method', []);

        $this->assertTrue($result);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSendRequestError()
    {
        $this->tokenService->method('getToken')->willReturn('sample-token');

        $this->httpClient->method('request')->willThrowException(
            new RequestException('Request failed', $this->createMock(\Psr\Http\Message\RequestInterface::class))
        );

        $this->expectException(RequestException::class);

        $this->apiClient->sendRequest('getToken', ['login' => 'vakhmierov', 'password' => 'wrong_password']);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSendRequestNoToken()
    {
        $this->tokenService->method('getToken')->willReturn('');  // Возвращаем пустую строку, если токен отсутствует

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to retrieve token');

        $this->apiClient->sendRequest('method', []);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSendRequestNoResult()
    {
        $this->tokenService->method('getToken')->willReturn('sample-token');

        $bodyStream = Utils::streamFor(json_encode([]));
        $mockResponse = new Response(200, [], $bodyStream);

        $this->httpClient->method('request')->willReturn($mockResponse);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API returned null response');

        $this->apiClient->sendRequest('method', []);
    }
}

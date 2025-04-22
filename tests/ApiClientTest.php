<?php

use PHPUnit\Framework\TestCase;
use App\API\ApiClient;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class ApiClientTest extends TestCase
{
    private $httpClient;
    private $logger;
    private $apiClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->apiClient = new ApiClient($this->httpClient, $this->logger);
    }

    // Тестирование успешного ответа от API

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSendRequestSuccess()
    {
        $response = new Response(200, [], '{"jsonrpc": "2.0", "result": "success", "id": 1}');
        $this->httpClient->method('post')->willReturn($response);

        $result = $this->apiClient->sendRequest('getToken', ['login' => 'vakhmierov', 'password' => 'tHYchuT94DE_WQbW']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('jsonrpc', $result);
        $this->assertEquals('success', $result['result']);
    }



    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSendRequestError()
    {
        $this->httpClient->method('post')->willThrowException(new RequestException('Request failed', $this->createMock(\Psr\Http\Message\RequestInterface::class)));

        $this->expectException(RequestException::class);

        $this->apiClient->sendRequest('getToken', ['login' => 'vakhmierov', 'password' => 'wrong_password']);
    }
}

<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\LoggerFactory;
use App\API\ApiClient;
use App\API\TokenService;
use App\API\DomainService;
use App\Config\Config;
use GuzzleHttp\Client as HttpClient;

try {
    ini_set('memory_limit', '1024M'); // Увеличиваем память, если необходимо

    $config = Config::load();
    $logger = LoggerFactory::create(); // Создание логгера

    $baseUri = $config->get('API_URL');
    $username = $config->get('API_USERNAME');  // Получаем логин
    $password = $config->get('API_PASSWORD');  // Получаем пароль

    $httpClient = new HttpClient();

    $apiClient = new ApiClient($httpClient, $logger, $baseUri);

    $tokenService = new TokenService($apiClient, $logger, $username, $password);

    $apiClient->setTokenService($tokenService);

    $token = $tokenService->getToken();

    $domainService = new DomainService($apiClient, $logger, $tokenService);
    $domainName = 'test-domain.ru';
    $addedDomain = $domainService->addDomain($domainName);
    $logger->info('Домен успешно добавлен', $addedDomain);

    echo "Домен добавлен: {$addedDomain['domain']}" . PHP_EOL;

} catch (\Exception $e) {
    $logger->error('Ошибка при выполнении операции', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]);
    echo "Ошибка: " . $e->getMessage() . PHP_EOL;
}

<?php
namespace App\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class LoggerFactory
{
    public static function create(): Logger
    {
        $logger = new Logger('app');

        $logDir = getenv('LOG_DIR') ?: __DIR__ . '/../../logs';

        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0777, true) && !is_dir($logDir)) {
                throw new \RuntimeException("Не удалось создать директорию для логов: $logDir");
            }
        }

        $logFile = $logDir . '/app.log';

        $handler = new StreamHandler($logFile, Logger::DEBUG);

        $formatter = new LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return $logger;
    }
}

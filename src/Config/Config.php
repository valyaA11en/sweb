<?php
namespace App\Config;

class Config
{
    private array $env;

    public function __construct(string $envPath)
    {
        if (!file_exists($envPath)) {
            throw new \InvalidArgumentException("Config file not found: $envPath");
        }

        $this->env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);
    }

    public function get(string $key, $default = null)
    {
        return $this->env[$key] ?? $default;
    }

    public static function load(string $envPath = null): self
    {
        if ($envPath === null) {
            $envPath = 'D:\it\OSPanel\domains\sweb\.env';  // Задаем полный путь
        }

        echo "Looking for config file at: $envPath\n";

        if (!$envPath || !file_exists($envPath)) {
            throw new \InvalidArgumentException("Config file not found: $envPath");
        }

        return new self($envPath);
    }
}


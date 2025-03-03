<?php
namespace Summit\Core;

class Config {
    private static $instance = null;
    private $config = [];

    private function __construct() {
        $this->loadConfigurations();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get($key, $default = null) {
        $instance = self::getInstance();
        return $instance->getValue($key, $default);
    }

    public static function set($key, $value) {
        $instance = self::getInstance();
        $instance->setValue($key, $value);
    }

    private function getValue($key, $default = null) {
        $parts = explode('.', $key);
        $config = $this->config;

        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                return $default;
            }
            $config = $config[$part];
        }

        return $config;
    }

    private function setValue($key, $value) {
        $parts = explode('.', $key);
        $config = &$this->config;

        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $config[$part] = $value;
            } else {
                if (!isset($config[$part]) || !is_array($config[$part])) {
                    $config[$part] = [];
                }
                $config = &$config[$part];
            }
        }
    }

    private function loadConfigurations() {
        // Load all configuration files from the config directory
        $configPath = __DIR__ . '/../../config/';
        $configFiles = glob($configPath . '*.php');

        foreach ($configFiles as $file) {
            $key = basename($file, '.php');
            $this->config[$key] = require $file;
        }
    }
}

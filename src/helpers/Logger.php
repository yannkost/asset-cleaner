<?php

declare(strict_types=1);

namespace yann\assetcleaner\helpers;

use Craft;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;

/**
 * Logger Helper
 *
 * Provides dedicated error logging for the Asset Cleaner plugin
 *
 * @since 1.0.0
 */
class Logger
{
    private static ?LoggerInterface $logger = null;

    /**
     * Get the plugin's dedicated logger instance
     *
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        if (self::$logger === null) {
            self::$logger = self::createLogger();
        }

        return self::$logger;
    }

    /**
     * Create the Monolog logger instance
     *
     * @return LoggerInterface
     */
    private static function createLogger(): LoggerInterface
    {
        $logger = new MonologLogger('asset-cleaner');

        // Create log file path in Craft's storage/logs directory with timestamp
        $timestamp = date('Y-m-d');
        $logPath = Craft::$app->getPath()->getLogPath() . '/asset-cleaner_' . $timestamp . '.log';

        // Create stream handler for error level and above
        $handler = new StreamHandler($logPath, MonologLogger::DEBUG);

        // Set a clean format
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * Log an error message
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    /**
     * Log an error with exception details
     *
     * @param string $message The error message
     * @param \Throwable $exception The exception to log
     * @param array $context Additional context data
     * @return void
     */
    public static function exception(string $message, \Throwable $exception, array $context = []): void
    {
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        self::getLogger()->error($message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message The info message
     * @param array $context Additional context data
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message The warning message
     * @param array $context Additional context data
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    /**
     * Log a debug message
     *
     * @param string $message The debug message
     * @param array $context Additional context data
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }
}

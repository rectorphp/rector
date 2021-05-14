<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210514\Tracy\Bridges\Psr;

use RectorPrefix20210514\Psr;
use RectorPrefix20210514\Tracy;
/**
 * Tracy\ILogger to Psr\Log\LoggerInterface adapter.
 */
class TracyToPsrLoggerAdapter extends \RectorPrefix20210514\Psr\Log\AbstractLogger
{
    /** PSR-3 log level to Tracy logger level mapping */
    private const LEVEL_MAP = [\RectorPrefix20210514\Psr\Log\LogLevel::EMERGENCY => \RectorPrefix20210514\Tracy\ILogger::CRITICAL, \RectorPrefix20210514\Psr\Log\LogLevel::ALERT => \RectorPrefix20210514\Tracy\ILogger::CRITICAL, \RectorPrefix20210514\Psr\Log\LogLevel::CRITICAL => \RectorPrefix20210514\Tracy\ILogger::CRITICAL, \RectorPrefix20210514\Psr\Log\LogLevel::ERROR => \RectorPrefix20210514\Tracy\ILogger::ERROR, \RectorPrefix20210514\Psr\Log\LogLevel::WARNING => \RectorPrefix20210514\Tracy\ILogger::WARNING, \RectorPrefix20210514\Psr\Log\LogLevel::NOTICE => \RectorPrefix20210514\Tracy\ILogger::WARNING, \RectorPrefix20210514\Psr\Log\LogLevel::INFO => \RectorPrefix20210514\Tracy\ILogger::INFO, \RectorPrefix20210514\Psr\Log\LogLevel::DEBUG => \RectorPrefix20210514\Tracy\ILogger::DEBUG];
    /** @var Tracy\ILogger */
    private $tracyLogger;
    public function __construct(\RectorPrefix20210514\Tracy\ILogger $tracyLogger)
    {
        $this->tracyLogger = $tracyLogger;
    }
    public function log($level, $message, array $context = [])
    {
        $level = self::LEVEL_MAP[$level] ?? \RectorPrefix20210514\Tracy\ILogger::ERROR;
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $this->tracyLogger->log($context['exception'], $level);
            unset($context['exception']);
        }
        if ($context) {
            $message = ['message' => $message, 'context' => $context];
        }
        $this->tracyLogger->log($message, $level);
    }
}

<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210510\Tracy\Bridges\Psr;

use RectorPrefix20210510\Psr;
use RectorPrefix20210510\Tracy;
/**
 * Tracy\ILogger to Psr\Log\LoggerInterface adapter.
 */
class TracyToPsrLoggerAdapter extends Psr\Log\AbstractLogger
{
    /** PSR-3 log level to Tracy logger level mapping */
    private const LEVEL_MAP = [Psr\Log\LogLevel::EMERGENCY => Tracy\ILogger::CRITICAL, Psr\Log\LogLevel::ALERT => Tracy\ILogger::CRITICAL, Psr\Log\LogLevel::CRITICAL => Tracy\ILogger::CRITICAL, Psr\Log\LogLevel::ERROR => Tracy\ILogger::ERROR, Psr\Log\LogLevel::WARNING => Tracy\ILogger::WARNING, Psr\Log\LogLevel::NOTICE => Tracy\ILogger::WARNING, Psr\Log\LogLevel::INFO => Tracy\ILogger::INFO, Psr\Log\LogLevel::DEBUG => Tracy\ILogger::DEBUG];
    /** @var Tracy\ILogger */
    private $tracyLogger;
    public function __construct(Tracy\ILogger $tracyLogger)
    {
        $this->tracyLogger = $tracyLogger;
    }
    public function log($level, $message, array $context = [])
    {
        $level = self::LEVEL_MAP[$level] ?? Tracy\ILogger::ERROR;
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

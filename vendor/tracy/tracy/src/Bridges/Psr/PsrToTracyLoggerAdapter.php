<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211106\Tracy\Bridges\Psr;

use RectorPrefix20211106\Psr;
use RectorPrefix20211106\Tracy;
/**
 * Psr\Log\LoggerInterface to Tracy\ILogger adapter.
 */
class PsrToTracyLoggerAdapter implements \RectorPrefix20211106\Tracy\ILogger
{
    /** Tracy logger level to PSR-3 log level mapping */
    private const LEVEL_MAP = [\RectorPrefix20211106\Tracy\ILogger::DEBUG => \RectorPrefix20211106\Psr\Log\LogLevel::DEBUG, \RectorPrefix20211106\Tracy\ILogger::INFO => \RectorPrefix20211106\Psr\Log\LogLevel::INFO, \RectorPrefix20211106\Tracy\ILogger::WARNING => \RectorPrefix20211106\Psr\Log\LogLevel::WARNING, \RectorPrefix20211106\Tracy\ILogger::ERROR => \RectorPrefix20211106\Psr\Log\LogLevel::ERROR, \RectorPrefix20211106\Tracy\ILogger::EXCEPTION => \RectorPrefix20211106\Psr\Log\LogLevel::ERROR, \RectorPrefix20211106\Tracy\ILogger::CRITICAL => \RectorPrefix20211106\Psr\Log\LogLevel::CRITICAL];
    /** @var Psr\Log\LoggerInterface */
    private $psrLogger;
    public function __construct(\RectorPrefix20211106\Psr\Log\LoggerInterface $psrLogger)
    {
        $this->psrLogger = $psrLogger;
    }
    public function log($value, $level = self::INFO)
    {
        if ($value instanceof \Throwable) {
            $message = \RectorPrefix20211106\Tracy\Helpers::getClass($value) . ': ' . $value->getMessage() . ($value->getCode() ? ' #' . $value->getCode() : '') . ' in ' . $value->getFile() . ':' . $value->getLine();
            $context = ['exception' => $value];
        } elseif (!\is_string($value)) {
            $message = \trim(\RectorPrefix20211106\Tracy\Dumper::toText($value));
            $context = [];
        } else {
            $message = $value;
            $context = [];
        }
        $this->psrLogger->log(self::LEVEL_MAP[$level] ?? \RectorPrefix20211106\Psr\Log\LogLevel::ERROR, $message, $context);
    }
}

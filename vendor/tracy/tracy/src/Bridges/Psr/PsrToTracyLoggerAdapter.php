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
 * Psr\Log\LoggerInterface to Tracy\ILogger adapter.
 */
class PsrToTracyLoggerAdapter implements Tracy\ILogger
{
    /** Tracy logger level to PSR-3 log level mapping */
    private const LEVEL_MAP = [Tracy\ILogger::DEBUG => Psr\Log\LogLevel::DEBUG, Tracy\ILogger::INFO => Psr\Log\LogLevel::INFO, Tracy\ILogger::WARNING => Psr\Log\LogLevel::WARNING, Tracy\ILogger::ERROR => Psr\Log\LogLevel::ERROR, Tracy\ILogger::EXCEPTION => Psr\Log\LogLevel::ERROR, Tracy\ILogger::CRITICAL => Psr\Log\LogLevel::CRITICAL];
    /** @var Psr\Log\LoggerInterface */
    private $psrLogger;
    public function __construct(Psr\Log\LoggerInterface $psrLogger)
    {
        $this->psrLogger = $psrLogger;
    }
    public function log($value, $level = self::INFO)
    {
        if ($value instanceof \Throwable) {
            $message = Tracy\Helpers::getClass($value) . ': ' . $value->getMessage() . ($value->getCode() ? ' #' . $value->getCode() : '') . ' in ' . $value->getFile() . ':' . $value->getLine();
            $context = ['exception' => $value];
        } elseif (!\is_string($value)) {
            $message = \trim(Tracy\Dumper::toText($value));
            $context = [];
        } else {
            $message = $value;
            $context = [];
        }
        $this->psrLogger->log(self::LEVEL_MAP[$level] ?? Psr\Log\LogLevel::ERROR, $message, $context);
    }
}

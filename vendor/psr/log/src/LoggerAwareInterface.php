<?php

namespace RectorPrefix202509\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     */
    public function setLogger(LoggerInterface $logger): void;
}

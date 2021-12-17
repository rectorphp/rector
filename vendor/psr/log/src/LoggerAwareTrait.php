<?php

namespace RectorPrefix20211217\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;
    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(\RectorPrefix20211217\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

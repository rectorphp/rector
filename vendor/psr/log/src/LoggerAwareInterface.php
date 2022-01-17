<?php

namespace RectorPrefix20220117\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(\RectorPrefix20220117\Psr\Log\LoggerInterface $logger);
}

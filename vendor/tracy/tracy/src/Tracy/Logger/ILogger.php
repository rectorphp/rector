<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Tracy;

/**
 * Logger.
 */
interface ILogger
{
    public const DEBUG = 'debug', INFO = 'info', WARNING = 'warning', ERROR = 'error', EXCEPTION = 'exception', CRITICAL = 'critical';
    /**
     * @param mixed $value
     */
    function log($value, string $level = self::INFO);
}

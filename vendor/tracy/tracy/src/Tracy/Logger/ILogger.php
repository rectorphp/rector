<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Tracy;

/**
 * Logger.
 */
interface ILogger
{
    const DEBUG = 'debug', INFO = 'info', WARNING = 'warning', ERROR = 'error', EXCEPTION = 'exception', CRITICAL = 'critical';
    function log($value, $level = self::INFO);
}

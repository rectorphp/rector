<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\Log;

use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
/**
 * DebugLoggerInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface DebugLoggerInterface
{
    /**
     * Returns an array of logs.
     *
     * A log is an array with the following mandatory keys:
     * timestamp, message, priority, and priorityName.
     * It can also have an optional context key containing an array.
     *
     * @return array An array of logs
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     */
    public function getLogs($request = null);
    /**
     * Returns the number of errors.
     *
     * @return int The number of errors
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     */
    public function countErrors($request = null);
    /**
     * Removes all log records.
     */
    public function clear();
}

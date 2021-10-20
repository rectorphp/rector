<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\ErrorHandler;

use RectorPrefix20211020\Psr\Log\AbstractLogger;
/**
 * A buffering logger that stacks logs for later.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class BufferingLogger extends \RectorPrefix20211020\Psr\Log\AbstractLogger
{
    private $logs = [];
    /**
     * @param mixed[] $context
     */
    public function log($level, $message, $context = []) : void
    {
        $this->logs[] = [$level, $message, $context];
    }
    public function cleanLogs() : array
    {
        $logs = $this->logs;
        $this->logs = [];
        return $logs;
    }
    /**
     * @return array
     */
    public function __sleep()
    {
        throw new \BadMethodCallException('Cannot serialize ' . __CLASS__);
    }
    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . __CLASS__);
    }
    public function __destruct()
    {
        foreach ($this->logs as [$level, $message, $context]) {
            if (\false !== \strpos($message, '{')) {
                foreach ($context as $key => $val) {
                    if (null === $val || \is_scalar($val) || \is_object($val) && \is_callable([$val, '__toString'])) {
                        $message = \str_replace("{{$key}}", $val, $message);
                    } elseif ($val instanceof \DateTimeInterface) {
                        $message = \str_replace("{{$key}}", $val->format(\DateTime::RFC3339), $message);
                    } elseif (\is_object($val)) {
                        $message = \str_replace("{{$key}}", '[object ' . \get_class($val) . ']', $message);
                    } else {
                        $message = \str_replace("{{$key}}", '[' . \gettype($val) . ']', $message);
                    }
                }
            }
            \error_log(\sprintf('%s [%s] %s', \date(\DateTime::RFC3339), $level, $message));
        }
    }
}

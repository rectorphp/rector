<?php

declare (strict_types=1);
namespace Rector\Core\Exception;

use Exception;
use Throwable;
final class ShouldNotHappenException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct($message = '', $code = 0, ?\Throwable $throwable = null)
    {
        if ($message === '') {
            $message = $this->createDefaultMessageWithLocation();
        }
        parent::__construct($message, $code, $throwable);
    }
    private function createDefaultMessageWithLocation() : string
    {
        $debugBacktrace = \debug_backtrace();
        $class = $debugBacktrace[2]['class'] ?? null;
        $function = (string) $debugBacktrace[2]['function'];
        $line = (int) $debugBacktrace[1]['line'];
        $method = $class ? $class . '::' . $function : $function;
        return \sprintf('Look at "%s()" on line %d', $method, $line);
    }
}

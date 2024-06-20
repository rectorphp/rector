<?php

declare (strict_types=1);
namespace Rector\Exception;

use Exception;
use Throwable;
final class ShouldNotHappenException extends Exception
{
    /**
     * @param int $code
     */
    public function __construct(string $message = '', $code = 0, ?Throwable $throwable = null)
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
        $function = $debugBacktrace[2]['function'];
        $line = $debugBacktrace[1]['line'] ?? 0;
        $method = $class !== null ? $class . '::' . $function : $function;
        /** @var string $method */
        /** @var int $line */
        return \sprintf('Look at "%s()" on line %d', $method, $line);
    }
}

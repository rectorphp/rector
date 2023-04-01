<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Nette\Utils;

use RectorPrefix202304\Nette;
use function is_array, is_object, is_string;
/**
 * PHP callable tools.
 */
final class Callback
{
    use Nette\StaticClass;
    /**
     * @param  string|object|callable  $callable  class, object, callable
     * @deprecated use Closure::fromCallable()
     */
    public static function closure($callable, ?string $method = null) : \Closure
    {
        \trigger_error(__METHOD__ . '() is deprecated, use Closure::fromCallable().', \E_USER_DEPRECATED);
        try {
            return \Closure::fromCallable($method === null ? $callable : [$callable, $method]);
        } catch (\TypeError $e) {
            throw new Nette\InvalidArgumentException($e->getMessage());
        }
    }
    /**
     * Invokes callback.
     * @return mixed
     * @deprecated
     */
    public static function invoke($callable, ...$args)
    {
        \trigger_error(__METHOD__ . '() is deprecated, use native invoking.', \E_USER_DEPRECATED);
        self::check($callable);
        return $callable(...$args);
    }
    /**
     * Invokes callback with an array of parameters.
     * @return mixed
     * @deprecated
     */
    public static function invokeArgs($callable, array $args = [])
    {
        \trigger_error(__METHOD__ . '() is deprecated, use native invoking.', \E_USER_DEPRECATED);
        self::check($callable);
        return $callable(...$args);
    }
    /**
     * Invokes internal PHP function with own error handler.
     * @return mixed
     */
    public static function invokeSafe(string $function, array $args, callable $onError)
    {
        $prev = \set_error_handler(function ($severity, $message, $file) use($onError, &$prev, $function) : ?bool {
            if ($file === __FILE__) {
                $msg = \ini_get('html_errors') ? Html::htmlToText($message) : $message;
                $msg = \preg_replace("#^{$function}\\(.*?\\): #", '', $msg);
                if ($onError($msg, $severity) !== \false) {
                    return null;
                }
            }
            return $prev ? $prev(...\func_get_args()) : \false;
        });
        try {
            return $function(...$args);
        } finally {
            \restore_error_handler();
        }
    }
    /**
     * Checks that $callable is valid PHP callback. Otherwise throws exception. If the $syntax is set to true, only verifies
     * that $callable has a valid structure to be used as a callback, but does not verify if the class or method actually exists.
     * @param  mixed  $callable
     * @return callable
     * @throws Nette\InvalidArgumentException
     */
    public static function check($callable, bool $syntax = \false)
    {
        if (!\is_callable($callable, $syntax)) {
            throw new Nette\InvalidArgumentException($syntax ? 'Given value is not a callable type.' : \sprintf("Callback '%s' is not callable.", self::toString($callable)));
        }
        return $callable;
    }
    /**
     * Converts PHP callback to textual form. Class or method may not exists.
     * @param  mixed  $callable
     */
    public static function toString($callable) : string
    {
        if ($callable instanceof \Closure) {
            $inner = self::unwrap($callable);
            return '{closure' . ($inner instanceof \Closure ? '}' : ' ' . self::toString($inner) . '}');
        } elseif (is_string($callable) && $callable[0] === "\x00") {
            return '{lambda}';
        } else {
            \is_callable(is_object($callable) ? [$callable, '__invoke'] : $callable, \true, $textual);
            return $textual;
        }
    }
    /**
     * Returns reflection for method or function used in PHP callback.
     * @param  callable  $callable  type check is escalated to ReflectionException
     * @return \ReflectionMethod|\ReflectionFunction
     * @throws \ReflectionException  if callback is not valid
     */
    public static function toReflection($callable) : \ReflectionFunctionAbstract
    {
        if ($callable instanceof \Closure) {
            $callable = self::unwrap($callable);
        }
        if (is_string($callable) && \strpos($callable, '::')) {
            return new \ReflectionMethod($callable);
        } elseif (is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        } elseif (is_object($callable) && !$callable instanceof \Closure) {
            return new \ReflectionMethod($callable, '__invoke');
        } else {
            return new \ReflectionFunction($callable);
        }
    }
    /**
     * Checks whether PHP callback is function or static method.
     */
    public static function isStatic(callable $callable) : bool
    {
        return is_string(is_array($callable) ? $callable[0] : $callable);
    }
    /**
     * Unwraps closure created by Closure::fromCallable().
     * @return callable|array
     */
    public static function unwrap(\Closure $closure)
    {
        $r = new \ReflectionFunction($closure);
        if (\substr($r->name, -1) === '}') {
            return $closure;
        } elseif ($obj = $r->getClosureThis()) {
            return [$obj, $r->name];
        } elseif ($class = $r->getClosureScopeClass()) {
            return [$class->name, $r->name];
        } else {
            return $r->name;
        }
    }
}

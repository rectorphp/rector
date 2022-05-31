<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220531\Nette;

use RectorPrefix20220531\Nette\Utils\ObjectHelpers;
/**
 * Strict class for better experience.
 * - 'did you mean' hints
 * - access to undeclared members throws exceptions
 * - support for @property annotations
 * - support for calling event handlers stored in $onEvent via onEvent()
 */
trait SmartObject
{
    /**
     * @throws MemberAccessException
     */
    public function __call(string $name, array $args)
    {
        $class = static::class;
        if (\RectorPrefix20220531\Nette\Utils\ObjectHelpers::hasProperty($class, $name) === 'event') {
            // calling event handlers
            $handlers = $this->{$name} ?? null;
            if (\is_iterable($handlers)) {
                foreach ($handlers as $handler) {
                    $handler(...$args);
                }
            } elseif ($handlers !== null) {
                throw new \RectorPrefix20220531\Nette\UnexpectedValueException("Property {$class}::\${$name} must be iterable or null, " . \gettype($handlers) . ' given.');
            }
        } else {
            \RectorPrefix20220531\Nette\Utils\ObjectHelpers::strictCall($class, $name);
        }
    }
    /**
     * @throws MemberAccessException
     */
    public static function __callStatic(string $name, array $args)
    {
        \RectorPrefix20220531\Nette\Utils\ObjectHelpers::strictStaticCall(static::class, $name);
    }
    /**
     * @return mixed
     * @throws MemberAccessException if the property is not defined.
     */
    public function &__get(string $name)
    {
        $class = static::class;
        if ($prop = \RectorPrefix20220531\Nette\Utils\ObjectHelpers::getMagicProperties($class)[$name] ?? null) {
            // property getter
            if (!($prop & 0b1)) {
                throw new \RectorPrefix20220531\Nette\MemberAccessException("Cannot read a write-only property {$class}::\${$name}.");
            }
            $m = ($prop & 0b10 ? 'get' : 'is') . \ucfirst($name);
            if ($prop & 0b10000) {
                $trace = \debug_backtrace(0, 1)[0];
                // suppose this method is called from __call()
                $loc = isset($trace['file'], $trace['line']) ? " in {$trace['file']} on line {$trace['line']}" : '';
                \trigger_error("Property {$class}::\${$name} is deprecated, use {$class}::{$m}() method{$loc}.", \E_USER_DEPRECATED);
            }
            if ($prop & 0b100) {
                // return by reference
                return $this->{$m}();
            } else {
                $val = $this->{$m}();
                return $val;
            }
        } else {
            \RectorPrefix20220531\Nette\Utils\ObjectHelpers::strictGet($class, $name);
        }
    }
    /**
     * @param  mixed  $value
     * @return void
     * @throws MemberAccessException if the property is not defined or is read-only
     */
    public function __set(string $name, $value)
    {
        $class = static::class;
        if (\RectorPrefix20220531\Nette\Utils\ObjectHelpers::hasProperty($class, $name)) {
            // unsetted property
            $this->{$name} = $value;
        } elseif ($prop = \RectorPrefix20220531\Nette\Utils\ObjectHelpers::getMagicProperties($class)[$name] ?? null) {
            // property setter
            if (!($prop & 0b1000)) {
                throw new \RectorPrefix20220531\Nette\MemberAccessException("Cannot write to a read-only property {$class}::\${$name}.");
            }
            $m = 'set' . \ucfirst($name);
            if ($prop & 0b10000) {
                $trace = \debug_backtrace(0, 1)[0];
                // suppose this method is called from __call()
                $loc = isset($trace['file'], $trace['line']) ? " in {$trace['file']} on line {$trace['line']}" : '';
                \trigger_error("Property {$class}::\${$name} is deprecated, use {$class}::{$m}() method{$loc}.", \E_USER_DEPRECATED);
            }
            $this->{$m}($value);
        } else {
            \RectorPrefix20220531\Nette\Utils\ObjectHelpers::strictSet($class, $name);
        }
    }
    /**
     * @return void
     * @throws MemberAccessException
     */
    public function __unset(string $name)
    {
        $class = static::class;
        if (!\RectorPrefix20220531\Nette\Utils\ObjectHelpers::hasProperty($class, $name)) {
            throw new \RectorPrefix20220531\Nette\MemberAccessException("Cannot unset the property {$class}::\${$name}.");
        }
    }
    public function __isset(string $name) : bool
    {
        return isset(\RectorPrefix20220531\Nette\Utils\ObjectHelpers::getMagicProperties(static::class)[$name]);
    }
}

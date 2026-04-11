<?php

declare (strict_types=1);
namespace RectorPrefix202604\Webmozart\Assert;

use ArrayAccess;
use Countable;
use Throwable;
/**
 * This trait provides nullOr*, all* and allNullOr* variants of assertion base methods.
 * Do not use this trait directly: it will change, and is not designed for reuse.
 */
trait Mixin
{
    /**
     * @psalm-pure
     *
     * @psalm-assert string|null $value
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrString($value, $message = '')
    {
        null === $value || static::string($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allString($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::string($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrString($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::string($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert non-empty-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return non-empty-string|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrStringNotEmpty($value, $message = '')
    {
        null === $value || static::stringNotEmpty($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-string>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allStringNotEmpty($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::stringNotEmpty($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrStringNotEmpty($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::stringNotEmpty($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert int|null $value
     *
     * @param string|callable():string $message
     *
     * @return int|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrInteger($value, $message = '')
    {
        null === $value || static::integer($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<int>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::integer($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<int|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::integer($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert numeric|null $value
     *
     * @param string|callable():string $message
     *
     * @return numeric|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIntegerish($value, $message = '')
    {
        null === $value || static::integerish($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIntegerish($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::integerish($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIntegerish($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::integerish($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert positive-int|null $value
     *
     * @param string|callable():string $message
     *
     * @return positive-int|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrPositiveInteger($value, $message = '')
    {
        null === $value || static::positiveInteger($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allPositiveInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::positiveInteger($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrPositiveInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::positiveInteger($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert non-negative-int|null $value
     *
     * @param string|callable():string $message
     *
     * @return non-negative-int|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrNotNegativeInteger($value, $message = '')
    {
        null === $value || static::notNegativeInteger($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-negative-int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-negative-int>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNotNegativeInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notNegativeInteger($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-negative-int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-negative-int|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrNotNegativeInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notNegativeInteger($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert negative-int|null $value
     *
     * @param string|callable():string $message
     *
     * @return negative-int|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrNegativeInteger($value, $message = '')
    {
        null === $value || static::negativeInteger($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<negative-int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<negative-int>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNegativeInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::negativeInteger($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<negative-int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<negative-int|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrNegativeInteger($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::negativeInteger($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert float|null $value
     *
     * @param string|callable():string $message
     *
     * @return float|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrFloat($value, $message = '')
    {
        null === $value || static::float($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<float> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<float>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allFloat($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::float($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<float|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<float|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrFloat($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::float($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert numeric|null $value
     *
     * @param string|callable():string $message
     *
     * @return numeric|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrNumeric($value, $message = '')
    {
        null === $value || static::numeric($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNumeric($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::numeric($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrNumeric($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::numeric($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert positive-int|0|null $value
     *
     * @param string|callable():string $message
     *
     * @return positive-int|0|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrNatural($value, $message = '')
    {
        null === $value || static::natural($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int|0> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int|0>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNatural($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::natural($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int|0|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int|0|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrNatural($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::natural($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert bool|null $value
     *
     * @param string|callable():string $message
     *
     * @return bool|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrBoolean($value, $message = '')
    {
        null === $value || static::boolean($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<bool> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<bool>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allBoolean($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::boolean($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<bool|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<bool|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrBoolean($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::boolean($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert scalar|null $value
     *
     * @param string|callable():string $message
     *
     * @return scalar|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrScalar($value, $message = '')
    {
        null === $value || static::scalar($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<scalar> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<scalar>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allScalar($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::scalar($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<scalar|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<scalar|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrScalar($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::scalar($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert object|null $value
     *
     * @param string|callable():string $message
     *
     * @return object|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrObject($value, $message = '')
    {
        null === $value || static::object($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allObject($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::object($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrObject($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::object($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert object|string|null $value
     *
     * @param string|callable():string $message
     *
     * @return object|string|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrObjectish($value, $message = '')
    {
        null === $value || static::objectish($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object|string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object|string>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allObjectish($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::objectish($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object|string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object|string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrObjectish($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::objectish($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert resource|null $value
     *
     * @param string|callable():string $message
     *
     * @see https://www.php.net/manual/en/function.get-resource-type.php
     *
     * @return resource|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrResource($value, ?string $type = null, $message = '')
    {
        null === $value || static::resource($value, $type, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<resource> $value
     *
     * @param string|callable():string $message
     *
     * @see https://www.php.net/manual/en/function.get-resource-type.php
     *
     * @return iterable<resource>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allResource($value, ?string $type = null, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::resource($entry, $type, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<resource|null> $value
     *
     * @param string|callable():string $message
     *
     * @see https://www.php.net/manual/en/function.get-resource-type.php
     *
     * @return iterable<resource|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrResource($value, ?string $type = null, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::resource($entry, $type, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert callable|null $value
     *
     * @param string|callable():string $message
     *
     * @return callable|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIsCallable($value, $message = '')
    {
        null === $value || static::isCallable($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<callable> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<callable>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIsCallable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isCallable($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<callable|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<callable|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIsCallable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isCallable($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert array|null $value
     *
     * @param string|callable():string $message
     *
     * @return array|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIsArray($value, $message = '')
    {
        null === $value || static::isArray($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIsArray($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isArray($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIsArray($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isArray($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert array|ArrayAccess|null $value
     *
     * @param string|callable():string $message
     *
     * @return array|ArrayAccess|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIsArrayAccessible($value, $message = '')
    {
        null === $value || static::isArrayAccessible($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array|ArrayAccess> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array|ArrayAccess>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIsArrayAccessible($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isArrayAccessible($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array|ArrayAccess|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array|ArrayAccess|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIsArrayAccessible($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isArrayAccessible($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert countable|null $value
     *
     * @param string|callable():string $message
     *
     * @return countable|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIsCountable($value, $message = '')
    {
        null === $value || static::isCountable($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<countable> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<countable>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIsCountable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isCountable($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<countable|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<countable|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIsCountable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isCountable($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable|null $value
     *
     * @param string|callable():string $message
     *
     * @return iterable|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIsIterable($value, $message = '')
    {
        null === $value || static::isIterable($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<iterable> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<iterable>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIsIterable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isIterable($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<iterable|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<iterable|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIsIterable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isIterable($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert T|null $value
     *
     * @param string|callable():string $message
     *
     * @psalm-param class-string<T> $class
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function nullOrIsInstanceOf($value, $class, $message = '')
    {
        null === $value || static::isInstanceOf($value, $class, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert iterable<T> $value
     *
     * @param string|callable():string $message
     *
     * @psalm-param class-string<T> $class
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function allIsInstanceOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isInstanceOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert iterable<T|null> $value
     *
     * @param string|callable():string $message
     *
     * @psalm-param class-string<T> $class
     *
     * @return iterable<T|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function allNullOrIsInstanceOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isInstanceOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @template T of object
     *
     * @param string|callable():string $message
     *
     * @psalm-param class-string<T> $class
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function nullOrNotInstanceOf($value, $class, $message = '')
    {
        null === $value || static::notInstanceOf($value, $class, $message);
        return $value;
    }
    /**
     * @template T of object
     *
     * @param string|callable():string $message
     *
     * @psalm-param class-string<T> $class
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function allNotInstanceOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notInstanceOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @template T of object
     * @psalm-assert iterable<object|null> $value
     *
     * @param string|callable():string $message
     *
     * @psalm-param class-string<T> $class
     *
     * @return iterable<object|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function allNullOrNotInstanceOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notInstanceOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @template T of object
     * @psalm-assert T|null $value
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     * @param mixed $classes
     */
    public static function nullOrIsInstanceOfAny($value, $classes, $message = '')
    {
        null === $value || static::isInstanceOfAny($value, $classes, $message);
        return $value;
    }
    /**
     * @template T of object
     * @psalm-assert iterable<T> $value
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     * @param mixed $classes
     */
    public static function allIsInstanceOfAny($value, $classes, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isInstanceOfAny($entry, $classes, $message);
        }
        return $value;
    }
    /**
     * @template T of object
     * @psalm-assert iterable<T|null> $value
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return iterable<T|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $classes
     */
    public static function allNullOrIsInstanceOfAny($value, $classes, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isInstanceOfAny($entry, $classes, $message);
        }
        return $value;
    }
    /**
     * @template T
     * @psalm-assert T|null $value
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     * @param mixed $classes
     */
    public static function nullOrIsNotInstanceOfAny($value, $classes, $message = '')
    {
        null === $value || static::isNotInstanceOfAny($value, $classes, $message);
        return $value;
    }
    /**
     * @template T
     * @psalm-assert iterable<T> $value
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     * @param mixed $classes
     */
    public static function allIsNotInstanceOfAny($value, $classes, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isNotInstanceOfAny($entry, $classes, $message);
        }
        return $value;
    }
    /**
     * @template T
     * @psalm-assert iterable<T|null> $value
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return iterable<T|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $classes
     */
    public static function allNullOrIsNotInstanceOfAny($value, $classes, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isNotInstanceOfAny($entry, $classes, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert T|class-string<T>|null $value
     *
     * @param string|callable():string $message
     *
     * @return T|class-string<T>|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function nullOrIsAOf($value, $class, $message = '')
    {
        null === $value || static::isAOf($value, $class, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert iterable<T|class-string<T>> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<T|class-string<T>>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function allIsAOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isAOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert iterable<T|class-string<T>|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<T|class-string<T>|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $class
     */
    public static function allNullOrIsAOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isAOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $class
     */
    public static function nullOrIsNotA($value, $class, $message = '')
    {
        null === $value || static::isNotA($value, $class, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $class
     */
    public static function allIsNotA($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isNotA($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<object|class-string|null> $value
     *
     * @param mixed $value
     * @param string|callable():string $message
     *
     * @return iterable<object|class-string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $class
     */
    public static function allNullOrIsNotA($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isNotA($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $value
     * @param mixed $classes
     * @param string|callable():string $message
     *
     * @psalm-param array<class-string> $classes
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsAnyOf($value, $classes, $message = '')
    {
        null === $value || static::isAnyOf($value, $classes, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $value
     * @param mixed $classes
     * @param string|callable():string $message
     *
     * @psalm-param array<class-string> $classes
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function allIsAnyOf($value, $classes, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isAnyOf($entry, $classes, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $value
     * @param mixed $classes
     * @param string|callable():string     $message
     *
     * @psalm-param array<class-string> $classes
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsAnyOf($value, $classes, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isAnyOf($entry, $classes, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert empty $value
     *
     * @param string|callable():string $message
     *
     * @return empty
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIsEmpty($value, $message = '')
    {
        null === $value || static::isEmpty($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<empty> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<empty>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIsEmpty($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::isEmpty($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<empty|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<empty|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIsEmpty($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::isEmpty($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrNotEmpty($value, $message = '')
    {
        null === $value || static::notEmpty($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNotEmpty($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notEmpty($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<!empty|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<!empty|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrNotEmpty($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notEmpty($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNull($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::null($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNotNull($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notNull($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert true|null $value
     *
     * @param string|callable():string $message
     *
     * @return true|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrTrue($value, $message = '')
    {
        null === $value || static::true($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<true> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<true>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allTrue($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::true($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<true|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<true|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrTrue($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::true($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert false|null $value
     *
     * @param string|callable():string $message
     *
     * @return false|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrFalse($value, $message = '')
    {
        null === $value || static::false($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<false> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<false>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allFalse($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::false($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<false|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<false|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrFalse($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::false($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrNotFalse($value, $message = '')
    {
        null === $value || static::notFalse($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNotFalse($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notFalse($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<!false|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<!false|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrNotFalse($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notFalse($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param string|null $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIp($value, $message = '')
    {
        null === $value || static::ip($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIp($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::ip($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string|null> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIp($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::ip($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param string|null $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIpv4($value, $message = '')
    {
        null === $value || static::ipv4($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIpv4($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::ipv4($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string|null> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIpv4($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::ipv4($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param string|null $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrIpv6($value, $message = '')
    {
        null === $value || static::ipv6($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allIpv6($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::ipv6($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string|null> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrIpv6($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::ipv6($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param string|null $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrEmail($value, $message = '')
    {
        null === $value || static::email($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allEmail($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::email($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @psalm-param iterable<string|null> $value
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrEmail($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::email($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $values
     */
    public static function nullOrUniqueValues($values, $message = '')
    {
        null === $values || static::uniqueValues($values, $message);
        return $values;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $values
     */
    public static function allUniqueValues($values, $message = '')
    {
        static::isIterable($values);
        foreach ($values as $entry) {
            static::uniqueValues($entry, $message);
        }
        return $values;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $values
     */
    public static function allNullOrUniqueValues($values, $message = '')
    {
        static::isIterable($values);
        foreach ($values as $entry) {
            null === $entry || static::uniqueValues($entry, $message);
        }
        return $values;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function nullOrEq($value, $expect, $message = '')
    {
        null === $value || static::eq($value, $expect, $message);
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allEq($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::eq($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allNullOrEq($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::eq($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function nullOrNotEq($value, $expect, $message = '')
    {
        null === $value || static::notEq($value, $expect, $message);
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allNotEq($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notEq($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allNullOrNotEq($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notEq($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function nullOrSame($value, $expect, $message = '')
    {
        null === $value || static::same($value, $expect, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allSame($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::same($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allNullOrSame($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::same($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function nullOrNotSame($value, $expect, $message = '')
    {
        null === $value || static::notSame($value, $expect, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allNotSame($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notSame($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     */
    public static function allNullOrNotSame($value, $expect, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notSame($entry, $expect, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function nullOrGreaterThan($value, $limit, $message = '')
    {
        null === $value || static::greaterThan($value, $limit, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allGreaterThan($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::greaterThan($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allNullOrGreaterThan($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::greaterThan($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function nullOrGreaterThanEq($value, $limit, $message = '')
    {
        null === $value || static::greaterThanEq($value, $limit, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allGreaterThanEq($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::greaterThanEq($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allNullOrGreaterThanEq($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::greaterThanEq($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function nullOrLessThan($value, $limit, $message = '')
    {
        null === $value || static::lessThan($value, $limit, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allLessThan($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::lessThan($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allNullOrLessThan($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::lessThan($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function nullOrLessThanEq($value, $limit, $message = '')
    {
        null === $value || static::lessThanEq($value, $limit, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allLessThanEq($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::lessThanEq($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     */
    public static function allNullOrLessThanEq($value, $limit, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::lessThanEq($entry, $limit, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     */
    public static function nullOrRange($value, $min, $max, $message = '')
    {
        null === $value || static::range($value, $min, $max, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     */
    public static function allRange($value, $min, $max, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::range($entry, $min, $max, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     */
    public static function allNullOrRange($value, $min, $max, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::range($entry, $min, $max, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function nullOrOneOf($value, $values, $message = '')
    {
        null === $value || static::oneOf($value, $values, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allOneOf($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::oneOf($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allNullOrOneOf($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::oneOf($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function nullOrInArray($value, $values, $message = '')
    {
        null === $value || static::inArray($value, $values, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allInArray($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::inArray($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allNullOrInArray($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::inArray($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function nullOrNotOneOf($value, $values, $message = '')
    {
        null === $value || static::notOneOf($value, $values, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allNotOneOf($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notOneOf($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allNullOrNotOneOf($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notOneOf($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function nullOrNotInArray($value, $values, $message = '')
    {
        null === $value || static::notInArray($value, $values, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allNotInArray($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notInArray($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     */
    public static function allNullOrNotInArray($value, $values, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notInArray($entry, $values, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function nullOrContains($value, $subString, $message = '')
    {
        null === $value || static::contains($value, $subString, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function allContains($value, $subString, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::contains($entry, $subString, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function allNullOrContains($value, $subString, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::contains($entry, $subString, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function nullOrNotContains($value, $subString, $message = '')
    {
        null === $value || static::notContains($value, $subString, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function allNotContains($value, $subString, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notContains($entry, $subString, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function allNullOrNotContains($value, $subString, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notContains($entry, $subString, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrNotWhitespaceOnly($value, $message = '')
    {
        null === $value || static::notWhitespaceOnly($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNotWhitespaceOnly($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notWhitespaceOnly($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrNotWhitespaceOnly($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notWhitespaceOnly($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function nullOrStartsWith($value, $prefix, $message = '')
    {
        null === $value || static::startsWith($value, $prefix, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function allStartsWith($value, $prefix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::startsWith($entry, $prefix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function allNullOrStartsWith($value, $prefix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::startsWith($entry, $prefix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function nullOrNotStartsWith($value, $prefix, $message = '')
    {
        null === $value || static::notStartsWith($value, $prefix, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function allNotStartsWith($value, $prefix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notStartsWith($entry, $prefix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function allNullOrNotStartsWith($value, $prefix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notStartsWith($entry, $prefix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrStartsWithLetter($value, $message = '')
    {
        null === $value || static::startsWithLetter($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allStartsWithLetter($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::startsWithLetter($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrStartsWithLetter($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::startsWithLetter($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function nullOrEndsWith($value, $suffix, $message = '')
    {
        null === $value || static::endsWith($value, $suffix, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function allEndsWith($value, $suffix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::endsWith($entry, $suffix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function allNullOrEndsWith($value, $suffix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::endsWith($entry, $suffix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function nullOrNotEndsWith($value, $suffix, $message = '')
    {
        null === $value || static::notEndsWith($value, $suffix, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function allNotEndsWith($value, $suffix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notEndsWith($entry, $suffix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function allNullOrNotEndsWith($value, $suffix, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notEndsWith($entry, $suffix, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function nullOrRegex($value, $pattern, $message = '')
    {
        null === $value || static::regex($value, $pattern, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function allRegex($value, $pattern, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::regex($entry, $pattern, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function allNullOrRegex($value, $pattern, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::regex($entry, $pattern, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function nullOrNotRegex($value, $pattern, $message = '')
    {
        null === $value || static::notRegex($value, $pattern, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function allNotRegex($value, $pattern, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::notRegex($entry, $pattern, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function allNullOrNotRegex($value, $pattern, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::notRegex($entry, $pattern, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrUnicodeLetters($value, $message = '')
    {
        null === $value || static::unicodeLetters($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allUnicodeLetters($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::unicodeLetters($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrUnicodeLetters($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::unicodeLetters($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrAlpha($value, $message = '')
    {
        null === $value || static::alpha($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allAlpha($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::alpha($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrAlpha($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::alpha($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrDigits($value, $message = '')
    {
        null === $value || static::digits($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allDigits($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::digits($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrDigits($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::digits($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrAlnum($value, $message = '')
    {
        null === $value || static::alnum($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allAlnum($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::alnum($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrAlnum($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::alnum($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert lowercase-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return lowercase-string|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrLower($value, $message = '')
    {
        null === $value || static::lower($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<lowercase-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<lowercase-string>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allLower($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::lower($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<lowercase-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<lowercase-string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrLower($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::lower($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrUpper($value, $message = '')
    {
        null === $value || static::upper($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allUpper($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::upper($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<!lowercase-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<!lowercase-string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrUpper($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::upper($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $length
     */
    public static function nullOrLength($value, $length, $message = '')
    {
        null === $value || static::length($value, $length, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $length
     */
    public static function allLength($value, $length, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::length($entry, $length, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $length
     */
    public static function allNullOrLength($value, $length, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::length($entry, $length, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     */
    public static function nullOrMinLength($value, $min, $message = '')
    {
        null === $value || static::minLength($value, $min, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     */
    public static function allMinLength($value, $min, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::minLength($entry, $min, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     */
    public static function allNullOrMinLength($value, $min, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::minLength($entry, $min, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $max
     */
    public static function nullOrMaxLength($value, $max, $message = '')
    {
        null === $value || static::maxLength($value, $max, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $max
     */
    public static function allMaxLength($value, $max, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::maxLength($entry, $max, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $max
     */
    public static function allNullOrMaxLength($value, $max, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::maxLength($entry, $max, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     */
    public static function nullOrLengthBetween($value, $min, $max, $message = '')
    {
        null === $value || static::lengthBetween($value, $min, $max, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     */
    public static function allLengthBetween($value, $min, $max, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::lengthBetween($entry, $min, $max, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     */
    public static function allNullOrLengthBetween($value, $min, $max, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::lengthBetween($entry, $min, $max, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrFileExists($value, $message = '')
    {
        null === $value || static::fileExists($value, $message);
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allFileExists($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::fileExists($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrFileExists($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::fileExists($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrFile($value, $message = '')
    {
        null === $value || static::file($value, $message);
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allFile($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::file($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrFile($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::file($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrDirectory($value, $message = '')
    {
        null === $value || static::directory($value, $message);
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allDirectory($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::directory($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrDirectory($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::directory($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrReadable($value, $message = '')
    {
        null === $value || static::readable($value, $message);
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allReadable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::readable($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrReadable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::readable($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrWritable($value, $message = '')
    {
        null === $value || static::writable($value, $message);
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allWritable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::writable($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrWritable($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::writable($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-assert class-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return class-string|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrClassExists($value, $message = '')
    {
        null === $value || static::classExists($value, $message);
        return $value;
    }
    /**
     * @psalm-assert iterable<class-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allClassExists($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::classExists($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-assert iterable<class-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrClassExists($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::classExists($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert class-string<ExpectedType>|null $value
     *
     * @param mixed $class
     * @param string|callable():string   $message
     *
     * @return class-string<ExpectedType>|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrSubclassOf($value, $class, $message = '')
    {
        null === $value || static::subclassOf($value, $class, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert iterable<class-string<ExpectedType>> $value
     *
     * @param mixed $class
     * @param string|callable():string   $message
     *
     * @return iterable<class-string<ExpectedType>>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allSubclassOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::subclassOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert iterable<class-string<ExpectedType>|null> $value
     *
     * @param mixed $class
     * @param string|callable():string   $message
     *
     * @return iterable<class-string<ExpectedType>|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrSubclassOf($value, $class, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::subclassOf($entry, $class, $message);
        }
        return $value;
    }
    /**
     * @psalm-assert class-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return class-string|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrInterfaceExists($value, $message = '')
    {
        null === $value || static::interfaceExists($value, $message);
        return $value;
    }
    /**
     * @psalm-assert iterable<class-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allInterfaceExists($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::interfaceExists($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-assert iterable<class-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrInterfaceExists($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::interfaceExists($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert class-string<ExpectedType>|ExpectedType|null $value
     *
     * @param mixed $value
     * @param mixed $interface
     * @param string|callable():string                     $message
     *
     * @return class-string<ExpectedType>|ExpectedType|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrImplementsInterface($value, $interface, $message = '')
    {
        null === $value || static::implementsInterface($value, $interface, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert iterable<class-string<ExpectedType>|ExpectedType> $value
     *
     * @param mixed $value
     * @param mixed $interface
     * @param string|callable():string                          $message
     *
     * @return iterable<class-string<ExpectedType>|ExpectedType>
     *
     * @throws InvalidArgumentException
     */
    public static function allImplementsInterface($value, $interface, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::implementsInterface($entry, $interface, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert iterable<class-string<ExpectedType>|ExpectedType|null> $value
     *
     * @param mixed $value
     * @param mixed $interface
     * @param string|callable():string                               $message
     *
     * @return iterable<class-string<ExpectedType>|ExpectedType|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrImplementsInterface($value, $interface, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::implementsInterface($entry, $interface, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $property
     */
    public static function nullOrPropertyExists($classOrObject, $property, $message = '')
    {
        null === $classOrObject || static::propertyExists($classOrObject, $property, $message);
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $property
     */
    public static function allPropertyExists($classOrObject, $property, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            static::propertyExists($entry, $property, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string     $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $property
     */
    public static function allNullOrPropertyExists($classOrObject, $property, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            null === $entry || static::propertyExists($entry, $property, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @psalm-param class-string|object|null $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $property
     */
    public static function nullOrPropertyNotExists($classOrObject, $property, $message = '')
    {
        null === $classOrObject || static::propertyNotExists($classOrObject, $property, $message);
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @psalm-param iterable<class-string|object> $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $property
     */
    public static function allPropertyNotExists($classOrObject, $property, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            static::propertyNotExists($entry, $property, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string     $message
     *
     * @psalm-param iterable<class-string|object|null> $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $property
     */
    public static function allNullOrPropertyNotExists($classOrObject, $property, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            null === $entry || static::propertyNotExists($entry, $property, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @psalm-param class-string|object|null $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $method
     */
    public static function nullOrMethodExists($classOrObject, $method, $message = '')
    {
        null === $classOrObject || static::methodExists($classOrObject, $method, $message);
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @psalm-param iterable<class-string|object> $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $method
     */
    public static function allMethodExists($classOrObject, $method, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            static::methodExists($entry, $method, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string     $message
     *
     * @psalm-param iterable<class-string|object|null> $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $method
     */
    public static function allNullOrMethodExists($classOrObject, $method, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            null === $entry || static::methodExists($entry, $method, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @psalm-param class-string|object|null $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $method
     */
    public static function nullOrMethodNotExists($classOrObject, $method, $message = '')
    {
        null === $classOrObject || static::methodNotExists($classOrObject, $method, $message);
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string $message
     *
     * @psalm-param iterable<class-string|object> $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $method
     */
    public static function allMethodNotExists($classOrObject, $method, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            static::methodNotExists($entry, $method, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @param string|callable():string     $message
     *
     * @psalm-param iterable<class-string|object|null> $classOrObject
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $method
     */
    public static function allNullOrMethodNotExists($classOrObject, $method, $message = '')
    {
        static::isIterable($classOrObject);
        foreach ($classOrObject as $entry) {
            null === $entry || static::methodNotExists($entry, $method, $message);
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function nullOrKeyExists($array, $key, $message = '')
    {
        null === $array || static::keyExists($array, $key, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allKeyExists($array, $key, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::keyExists($entry, $key, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allNullOrKeyExists($array, $key, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::keyExists($entry, $key, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function nullOrKeyNotExists($array, $key, $message = '')
    {
        null === $array || static::keyNotExists($array, $key, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allKeyNotExists($array, $key, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::keyNotExists($entry, $key, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allNullOrKeyNotExists($array, $key, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::keyNotExists($entry, $key, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert array-key|null $value
     *
     * @param string|callable():string $message
     *
     * @return array-key|null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrValidArrayKey($value, $message = '')
    {
        null === $value || static::validArrayKey($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array-key> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array-key>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allValidArrayKey($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::validArrayKey($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array-key|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array-key|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrValidArrayKey($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::validArrayKey($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $number
     */
    public static function nullOrCount($array, $number, $message = '')
    {
        null === $array || static::count($array, $number, $message);
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $number
     */
    public static function allCount($array, $number, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::count($entry, $number, $message);
        }
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $number
     */
    public static function allNullOrCount($array, $number, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::count($entry, $number, $message);
        }
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $min
     */
    public static function nullOrMinCount($array, $min, $message = '')
    {
        null === $array || static::minCount($array, $min, $message);
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $min
     */
    public static function allMinCount($array, $min, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::minCount($entry, $min, $message);
        }
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $min
     */
    public static function allNullOrMinCount($array, $min, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::minCount($entry, $min, $message);
        }
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $max
     */
    public static function nullOrMaxCount($array, $max, $message = '')
    {
        null === $array || static::maxCount($array, $max, $message);
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $max
     */
    public static function allMaxCount($array, $max, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::maxCount($entry, $max, $message);
        }
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $max
     */
    public static function allNullOrMaxCount($array, $max, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::maxCount($entry, $max, $message);
        }
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $min
     * @param mixed $max
     */
    public static function nullOrCountBetween($array, $min, $max, $message = '')
    {
        null === $array || static::countBetween($array, $min, $max, $message);
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $min
     * @param mixed $max
     */
    public static function allCountBetween($array, $min, $max, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::countBetween($entry, $min, $max, $message);
        }
        return $array;
    }
    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     * @param mixed $min
     * @param mixed $max
     */
    public static function allNullOrCountBetween($array, $min, $max, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::countBetween($entry, $min, $max, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert list<mixed>|null $array
     *
     * @param string|callable():string $message
     *
     * @return list<mixed>|null
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function nullOrIsList($array, $message = '')
    {
        null === $array || static::isList($array, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<list<mixed>> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<list<mixed>>
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allIsList($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::isList($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<list<mixed>|null> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<list<mixed>|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allNullOrIsList($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::isList($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert non-empty-list<mixed>|null $array
     *
     * @param string|callable():string $message
     *
     * @return non-empty-list<mixed>|null
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function nullOrIsNonEmptyList($array, $message = '')
    {
        null === $array || static::isNonEmptyList($array, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-list<mixed>> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-list<mixed>>
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allIsNonEmptyList($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::isNonEmptyList($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-list<mixed>|null> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-list<mixed>|null>
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function allNullOrIsNonEmptyList($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::isNonEmptyList($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert array<string, T>|null $array
     *
     * @param mixed $array
     * @param string|callable():string       $message
     *
     * @return array<string, T>|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsMap($array, $message = '')
    {
        null === $array || static::isMap($array, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<array<string, T>> $array
     *
     * @param mixed $array
     * @param string|callable():string            $message
     *
     * @return iterable<array<string, T>>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsMap($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::isMap($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<array<string, T>|null> $array
     *
     * @param mixed $array
     * @param string|callable():string                 $message
     *
     * @return iterable<array<string, T>|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsMap($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::isMap($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-assert callable|null $callable
     *
     * @param mixed $callable
     * @param string|callable():string $message
     *
     * @return callable|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsStatic($callable, $message = '')
    {
        null === $callable || static::isStatic($callable, $message);
        return $callable;
    }
    /**
     * @psalm-assert iterable<callable> $callable
     *
     * @param mixed $callable
     * @param string|callable():string   $message
     *
     * @return iterable<callable>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsStatic($callable, $message = '')
    {
        static::isIterable($callable);
        foreach ($callable as $entry) {
            static::isStatic($entry, $message);
        }
        return $callable;
    }
    /**
     * @psalm-assert iterable<callable|null> $callable
     *
     * @param mixed $callable
     * @param string|callable():string        $message
     *
     * @return iterable<callable|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsStatic($callable, $message = '')
    {
        static::isIterable($callable);
        foreach ($callable as $entry) {
            null === $entry || static::isStatic($entry, $message);
        }
        return $callable;
    }
    /**
     * @psalm-assert callable|null $callable
     *
     * @param mixed $callable
     * @param string|callable():string $message
     *
     * @return callable|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotStatic($callable, $message = '')
    {
        null === $callable || static::notStatic($callable, $message);
        return $callable;
    }
    /**
     * @psalm-assert iterable<callable> $callable
     *
     * @param mixed $callable
     * @param string|callable():string   $message
     *
     * @return iterable<callable>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotStatic($callable, $message = '')
    {
        static::isIterable($callable);
        foreach ($callable as $entry) {
            static::notStatic($entry, $message);
        }
        return $callable;
    }
    /**
     * @psalm-assert iterable<callable|null> $callable
     *
     * @param mixed $callable
     * @param string|callable():string        $message
     *
     * @return iterable<callable|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotStatic($callable, $message = '')
    {
        static::isIterable($callable);
        foreach ($callable as $entry) {
            null === $entry || static::notStatic($entry, $message);
        }
        return $callable;
    }
    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param mixed $array
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsNonEmptyMap($array, $message = '')
    {
        null === $array || static::isNonEmptyMap($array, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param mixed $array
     * @param string|callable():string   $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function allIsNonEmptyMap($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            static::isNonEmptyMap($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<array<string, T>|null> $array
     * @psalm-assert iterable<!empty|null> $array
     *
     * @param mixed $array
     * @param string|callable():string        $message
     *
     * @return iterable<!empty|null>
     *
     * @throws InvalidArgumentException
     * @return mixed
     */
    public static function allNullOrIsNonEmptyMap($array, $message = '')
    {
        static::isIterable($array);
        foreach ($array as $entry) {
            null === $entry || static::isNonEmptyMap($entry, $message);
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function nullOrUuid($value, $message = '')
    {
        null === $value || static::uuid($value, $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allUuid($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            static::uuid($entry, $message);
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function allNullOrUuid($value, $message = ''): iterable
    {
        static::isIterable($value);
        foreach ($value as $entry) {
            null === $entry || static::uuid($entry, $message);
        }
        return $value;
    }
    /**
     * @param string|callable():string $message
     *
     * @psalm-param class-string<Throwable> $class
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $expression
     */
    public static function nullOrThrows($expression, string $class = 'Throwable', $message = '')
    {
        null === $expression || static::throws($expression, $class, $message);
        return $expression;
    }
    /**
     * @param string|callable():string $message
     *
     * @psalm-param class-string<Throwable> $class
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $expression
     */
    public static function allThrows($expression, string $class = 'Throwable', $message = '')
    {
        static::isIterable($expression);
        foreach ($expression as $entry) {
            static::throws($entry, $class, $message);
        }
        return $expression;
    }
    /**
     * @param string|callable():string $message
     *
     * @psalm-param class-string<Throwable> $class
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @param mixed $expression
     */
    public static function allNullOrThrows($expression, string $class = 'Throwable', $message = '')
    {
        static::isIterable($expression);
        foreach ($expression as $entry) {
            null === $entry || static::throws($entry, $class, $message);
        }
        return $expression;
    }
}

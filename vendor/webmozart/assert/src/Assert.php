<?php

declare (strict_types=1);
/*
 * This file is part of the webmozart/assert package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202601\Webmozart\Assert;

use ArrayAccess;
use Closure;
use Countable;
use DateTime;
use DateTimeImmutable;
use ReflectionFunction;
use ReflectionProperty;
use Throwable;
use Traversable;
/**
 * Efficient assertions to validate the input/output of your methods.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Assert
{
    use Mixin;
    /**
     * @psalm-pure
     *
     * @psalm-assert string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function string($value, string $message = ''): string
    {
        if (!\is_string($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a string. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert non-empty-string $value
     *
     * @psalm-return non-empty-string
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function stringNotEmpty($value, string $message = ''): string
    {
        static::string($value, $message);
        static::notSame($value, '', $message);
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert int $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function integer($value, string $message = ''): int
    {
        if (!\is_int($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an integer. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert numeric $value
     *
     * @throws InvalidArgumentException
     * @return float|int|string
     * @param mixed $value
     */
    public static function integerish($value, string $message = '')
    {
        if (!\is_numeric($value) || $value != (int) $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an integerish value. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert positive-int $value
     *
     * @psalm-return positive-int
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function positiveInteger($value, string $message = ''): int
    {
        static::integer($value, $message);
        if ($value < 1) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a positive integer. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     * @psalm-assert non-negative-int $value
     *
     * @psalm-return non-negative-int
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function notNegativeInteger($value, string $message = ''): int
    {
        static::integer($value, $message);
        if ($value < 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a non negative integer. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     * @psalm-assert negative-int $value
     *
     * @psalm-return negative-int
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function negativeInteger($value, string $message = ''): int
    {
        static::integer($value, $message);
        if ($value >= 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a negative integer. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert float $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function float($value, string $message = ''): float
    {
        if (!\is_float($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a float. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert numeric $value
     *
     * @throws InvalidArgumentException
     * @return float|int|string
     * @param mixed $value
     */
    public static function numeric($value, string $message = '')
    {
        if (!\is_numeric($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a numeric. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert positive-int|0 $value
     *
     * @psalm-return positive-int|0
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function natural($value, string $message = ''): int
    {
        if (!\is_int($value) || $value < 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-negative integer. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert bool $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function boolean($value, string $message = ''): bool
    {
        if (!\is_bool($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a boolean. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert scalar $value
     *
     * @throws InvalidArgumentException
     * @return bool|float|int|string
     * @param mixed $value
     */
    public static function scalar($value, string $message = '')
    {
        if (!\is_scalar($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a scalar. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert object $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function object($value, string $message = ''): object
    {
        if (!\is_object($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an object. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert object|string $value
     *
     * @psalm-return object|string
     *
     * @throws InvalidArgumentException
     * @return object|string
     * @param mixed $value
     */
    public static function objectish($value, string $message = '')
    {
        if (!\is_object($value) && !\is_string($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an objectish value. Got: %s', static::typeToString($value)));
        }
        if (\is_string($value) && !\class_exists($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected class to be defined. Got: %s', $value));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert resource $value
     *
     * @see https://www.php.net/manual/en/function.get-resource-type.php
     *
     * @psalm-return resource
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @return mixed
     */
    public static function resource($value, ?string $type = null, string $message = '')
    {
        if (!\is_resource($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a resource. Got: %s', static::typeToString($value), $type));
        }
        if ($type && $type !== \get_resource_type($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a resource of type %2$s. Got: %s', static::typeToString($value), $type));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert object $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function isInitialized($value, string $property, string $message = ''): object
    {
        Assert::object($value);
        $reflectionProperty = new ReflectionProperty($value, $property);
        if (\PHP_VERSION_ID < 80100) {
            $reflectionProperty->setAccessible(\true);
        }
        if (!$reflectionProperty->isInitialized($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected property %s to be initialized.', $property));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert callable $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function isCallable($value, string $message = ''): callable
    {
        if (!\is_callable($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a callable. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert array $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function isArray($value, string $message = ''): array
    {
        if (!\is_array($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert array|ArrayAccess $value
     *
     * @throws InvalidArgumentException
     * @return mixed[]|\ArrayAccess
     * @param mixed $value
     */
    public static function isArrayAccessible($value, string $message = '')
    {
        if (!\is_array($value) && !$value instanceof ArrayAccess) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array accessible. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert countable $value
     *
     * @throws InvalidArgumentException
     * @return mixed[]|\Countable
     * @param mixed $value
     */
    public static function isCountable($value, string $message = '')
    {
        if (!\is_array($value) && !$value instanceof Countable) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a countable. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert iterable $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function isIterable($value, string $message = ''): iterable
    {
        if (!\is_array($value) && !$value instanceof Traversable) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an iterable. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     *
     * @psalm-assert ExpectedType $value
     *
     * @param mixed $class
     *
     * @return ExpectedType
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function isInstanceOf($value, $class, string $message = ''): object
    {
        static::string($class, 'Expected class as a string. Got: %s');
        if (!$value instanceof $class) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of %2$s. Got: %s', static::typeToString($value), $class));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     *
     * @psalm-assert !ExpectedType $value
     *
     * @param mixed $class
     *
     * @return !ExpectedType
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function notInstanceOf($value, $class, string $message = ''): object
    {
        static::object($value);
        static::string($class, 'Expected class as a string. Got: %s');
        if ($value instanceof $class) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance other than %2$s. Got: %s', static::typeToString($value), $class));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classes
     * @psalm-param array<class-string> $classes
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function isInstanceOfAny($value, $classes, string $message = ''): object
    {
        static::object($value);
        static::isIterable($classes);
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return $value;
            }
        }
        static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of any of %2$s. Got: %s', static::typeToString($value), \implode(', ', \array_map([static::class, 'valueToString'], \iterator_to_array(is_array($classes) ? new \ArrayIterator($classes) : $classes)))));
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     *
     * @psalm-assert ExpectedType|class-string<ExpectedType> $value
     *
     * @param mixed $value
     * @param mixed $class
     *
     * @return ExpectedType|class-string<ExpectedType>
     *
     * @throws InvalidArgumentException
     */
    public static function isAOf($value, $class, string $message = '')
    {
        static::string($class, 'Expected class as a string. Got: %s');
        if (!\is_a($value, $class, \is_string($value))) {
            static::reportInvalidArgument(sprintf($message ?: 'Expected an instance of this class or to this class among its parents "%2$s". Got: %s', static::valueToString($value), $class));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template UnexpectedType of object
     *
     * @param mixed $value
     * @param mixed $class
     *
     * @psalm-return !UnexpectedType
     *
     * @throws InvalidArgumentException
     * @return object|string
     */
    public static function isNotA($value, $class, string $message = '')
    {
        static::objectish($value);
        static::string($class, 'Expected class as a string. Got: %s');
        if (\is_a($value, $class, \is_string($value))) {
            static::reportInvalidArgument(sprintf($message ?: 'Expected an instance of this class or to this class among its parents other than "%2$s". Got: %s', static::valueToString($value), $class));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $value
     * @param mixed $classes
     * @psalm-param array<class-string> $classes
     *
     * @throws InvalidArgumentException
     * @return object|string
     */
    public static function isAnyOf($value, $classes, string $message = '')
    {
        static::isIterable($classes);
        foreach ($classes as $class) {
            static::objectish($value);
            static::string($class, 'Expected class as a string. Got: %s');
            if (\is_a($value, $class, \is_string($value))) {
                return $value;
            }
        }
        static::reportInvalidArgument(sprintf($message ?: 'Expected an instance of any of this classes or any of those classes among their parents "%2$s". Got: %s', static::valueToString($value), \implode(', ', \iterator_to_array(is_array($classes) ? new \ArrayIterator($classes) : $classes))));
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert empty $value
     *
     * @psalm-return empty
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @return mixed
     */
    public static function isEmpty($value, string $message = '')
    {
        if (!empty($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an empty value. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert !empty $value
     *
     * @psalm-return !empty
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @return mixed
     */
    public static function notEmpty($value, string $message = '')
    {
        if (empty($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-empty value. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert null $value
     *
     * @throws InvalidArgumentException
     * @return null
     * @param mixed $value
     */
    public static function null($value, string $message = '')
    {
        if (null !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected null. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert !null $value
     *
     * @psalm-return !null
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @return mixed
     */
    public static function notNull($value, string $message = '')
    {
        if (null === $value) {
            static::reportInvalidArgument($message ?: 'Expected a value other than null.');
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert true $value
     *
     * @throws InvalidArgumentException
     * @return true
     * @param mixed $value
     */
    public static function true($value, string $message = ''): bool
    {
        if (\true !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be true. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert false $value
     *
     * @throws InvalidArgumentException
     * @return false
     * @param mixed $value
     */
    public static function false($value, string $message = ''): bool
    {
        if (\false !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be false. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert !false $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @return mixed
     */
    public static function notFalse($value, string $message = '')
    {
        if (\false === $value) {
            static::reportInvalidArgument($message ?: 'Expected a value other than false.');
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-param string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function ip($value, string $message = ''): string
    {
        static::string($value, $message);
        if (\false === \filter_var($value, \FILTER_VALIDATE_IP)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IP. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-param string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function ipv4($value, string $message = ''): string
    {
        static::string($value, $message);
        if (\false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IPv4. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-param string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function ipv6($value, string $message = ''): string
    {
        static::string($value, $message);
        if (\false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IPv6. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-param string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function email($value, string $message = ''): string
    {
        static::string($value, $message);
        if (\false === \filter_var($value, \FILTER_VALIDATE_EMAIL, \FILTER_FLAG_EMAIL_UNICODE)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be a valid e-mail address. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * Does non-strict comparisons on the items, so ['3', 3] will not pass the assertion.
     *
     * @throws InvalidArgumentException
     * @param mixed $values
     */
    public static function uniqueValues($values, string $message = ''): array
    {
        static::isArray($values);
        $allValues = \count($values);
        $uniqueValues = \count(\array_unique($values));
        if ($allValues !== $uniqueValues) {
            $difference = $allValues - $uniqueValues;
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array of unique values, but %s of them %s duplicated', $difference, 1 === $difference ? 'is' : 'are'));
        }
        return $values;
    }
    /**
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     * @return mixed
     */
    public static function eq($value, $expect, string $message = '')
    {
        if ($expect != $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value equal to %2$s. Got: %s', static::valueToString($value), static::valueToString($expect)));
        }
        return $value;
    }
    /**
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     * @return mixed
     */
    public static function notEq($value, $expect, string $message = '')
    {
        if ($expect == $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a different value than %s.', static::valueToString($expect)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     * @return mixed
     */
    public static function same($value, $expect, string $message = '')
    {
        if ($expect !== $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value identical to %2$s. Got: %s', static::valueToString($value), static::valueToString($expect)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $expect
     * @return mixed
     */
    public static function notSame($value, $expect, string $message = '')
    {
        if ($expect === $value) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not identical to %s.', static::valueToString($expect)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     * @return mixed
     */
    public static function greaterThan($value, $limit, string $message = '')
    {
        if ($value <= $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value greater than %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     * @return mixed
     */
    public static function greaterThanEq($value, $limit, string $message = '')
    {
        if ($value < $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value greater than or equal to %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     * @return mixed
     */
    public static function lessThan($value, $limit, string $message = '')
    {
        if ($value >= $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value less than %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $limit
     * @return mixed
     */
    public static function lessThanEq($value, $limit, string $message = '')
    {
        if ($value > $limit) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value less than or equal to %2$s. Got: %s', static::valueToString($value), static::valueToString($limit)));
        }
        return $value;
    }
    /**
     * Inclusive range, so Assert::(3, 3, 5) passes.
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @return mixed
     */
    public static function range($value, $min, $max, string $message = '')
    {
        if ($value < $min || $value > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value between %2$s and %3$s. Got: %s', static::valueToString($value), static::valueToString($min), static::valueToString($max)));
        }
        return $value;
    }
    /**
     * A more human-readable alias of Assert::inArray().
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     * @return mixed
     */
    public static function oneOf($value, $values, string $message = '')
    {
        static::inArray($value, $values, $message);
        return $value;
    }
    /**
     * Does strict comparison, so Assert::inArray(3, ['3']) does not pass the assertion.
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     * @return mixed
     */
    public static function inArray($value, $values, string $message = '')
    {
        static::isArray($values);
        if (!\in_array($value, $values, \true)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected one of: %2$s. Got: %s', static::valueToString($value), \implode(', ', \array_map([static::class, 'valueToString'], $values))));
        }
        return $value;
    }
    /**
     * A more human-readable alias of Assert::notInArray().
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     * @return mixed
     */
    public static function notOneOf($value, $values, string $message = '')
    {
        static::notInArray($value, $values, $message);
        return $value;
    }
    /**
     * Check that a value is not present
     *
     * Does strict comparison, so Assert::notInArray(3, [1, 2, 3]) will not pass
     * the assertion, but Assert::notInArray(3, ['3']) will.
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $values
     * @return mixed
     */
    public static function notInArray($value, $values, string $message = '')
    {
        static::isArray($values);
        if (\in_array($value, $values, \true)) {
            static::reportInvalidArgument(\sprintf($message ?: '%2$s was not expected to contain a value. Got: %s', static::valueToString($value), \implode(', ', \array_map(['static', 'valueToString'], $values))));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function contains($value, $subString, string $message = ''): string
    {
        static::string($value);
        static::string($subString);
        if (strpos($value, $subString) === \false) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain %2$s. Got: %s', static::valueToString($value), static::valueToString($subString)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $subString
     */
    public static function notContains($value, $subString, string $message = ''): string
    {
        static::string($value);
        static::string($subString);
        if (strpos($value, $subString) !== \false) {
            static::reportInvalidArgument(\sprintf($message ?: '%2$s was not expected to be contained in a value. Got: %s', static::valueToString($value), static::valueToString($subString)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function notWhitespaceOnly($value, string $message = ''): string
    {
        static::string($value);
        if (\preg_match('/^\s*$/', $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-whitespace string. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function startsWith($value, $prefix, string $message = ''): string
    {
        static::string($value);
        static::string($prefix);
        if (strncmp($value, $prefix, strlen($prefix)) !== 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to start with %2$s. Got: %s', static::valueToString($value), static::valueToString($prefix)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $prefix
     */
    public static function notStartsWith($value, $prefix, string $message = ''): string
    {
        static::string($value);
        static::string($prefix);
        if (strncmp($value, $prefix, strlen($prefix)) === 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not to start with %2$s. Got: %s', static::valueToString($value), static::valueToString($prefix)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function startsWithLetter($value, string $message = ''): string
    {
        static::string($value);
        $valid = isset($value[0]);
        if ($valid) {
            $locale = \setlocale(\LC_CTYPE, '0');
            \setlocale(\LC_CTYPE, 'C');
            $valid = \ctype_alpha($value[0]);
            \setlocale(\LC_CTYPE, $locale);
        }
        if (!$valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to start with a letter. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function endsWith($value, $suffix, string $message = ''): string
    {
        static::string($value);
        static::string($suffix);
        if (substr_compare($value, $suffix, -strlen($suffix)) !== 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to end with %2$s. Got: %s', static::valueToString($value), static::valueToString($suffix)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $suffix
     */
    public static function notEndsWith($value, $suffix, string $message = ''): string
    {
        static::string($value);
        static::string($suffix);
        if (substr_compare($value, $suffix, -strlen($suffix)) === 0) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not to end with %2$s. Got: %s', static::valueToString($value), static::valueToString($suffix)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function regex($value, $pattern, string $message = ''): string
    {
        static::string($value);
        static::string($pattern);
        if (!\preg_match($pattern, $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The value %s does not match the expected pattern.', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $pattern
     */
    public static function notRegex($value, $pattern, string $message = ''): string
    {
        static::string($value);
        static::string($pattern);
        if (\preg_match($pattern, $value, $matches, \PREG_OFFSET_CAPTURE)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The value %s matches the pattern %s (at offset %d).', static::valueToString($value), static::valueToString($pattern), $matches[0][1]));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function unicodeLetters($value, string $message = ''): string
    {
        static::string($value, $message);
        if (!\preg_match('/^\p{L}+$/u', $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain only Unicode letters. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function alpha($value, string $message = ''): string
    {
        static::string($value, $message);
        $locale = \setlocale(\LC_CTYPE, '0');
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_alpha($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain only letters. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function digits($value, string $message = ''): string
    {
        static::string($value, $message);
        $locale = \setlocale(\LC_CTYPE, '0');
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_digit($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain digits only. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function alnum($value, string $message = ''): string
    {
        static::string($value, $message);
        $locale = \setlocale(\LC_CTYPE, '0');
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_alnum($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain letters and digits only. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert lowercase-string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function lower($value, string $message = ''): string
    {
        static::string($value, $message);
        $locale = \setlocale(\LC_CTYPE, '0');
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_lower($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain lowercase characters only. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert !lowercase-string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function upper($value, string $message = ''): string
    {
        static::string($value, $message);
        $locale = \setlocale(\LC_CTYPE, '0');
        \setlocale(\LC_CTYPE, 'C');
        $valid = !\ctype_upper($value);
        \setlocale(\LC_CTYPE, $locale);
        if ($valid) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain uppercase characters only. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $length
     */
    public static function length($value, $length, string $message = ''): string
    {
        static::string($value);
        static::integerish($length);
        if ($length !== static::strlen($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain %2$s characters. Got: %s', static::valueToString($value), $length));
        }
        return $value;
    }
    /**
     * Inclusive min.
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     */
    public static function minLength($value, $min, string $message = ''): string
    {
        static::string($value);
        static::integerish($min);
        if (static::strlen($value) < $min) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain at least %2$s characters. Got: %s', static::valueToString($value), $min));
        }
        return $value;
    }
    /**
     * Inclusive max.
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $max
     */
    public static function maxLength($value, $max, string $message = ''): string
    {
        static::string($value);
        static::integerish($max);
        if (static::strlen($value) > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain at most %2$s characters. Got: %s', static::valueToString($value), $max));
        }
        return $value;
    }
    /**
     * Inclusive, so Assert::lengthBetween('asd', 3, 5); passes the assertion.
     *
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     */
    public static function lengthBetween($value, $min, $max, string $message = ''): string
    {
        static::string($value);
        static::integerish($min);
        static::integerish($max);
        $length = static::strlen($value);
        if ($length < $min || $length > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s', static::valueToString($value), $min, $max));
        }
        return $value;
    }
    /**
     * Will also pass if $value is a directory, use Assert::file() instead if you need to be sure it is a file.
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function fileExists($value, string $message = ''): string
    {
        static::string($value);
        if (!\file_exists($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s does not exist.', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function file($value, string $message = ''): string
    {
        static::string($value);
        if (!\is_file($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not a file.', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function directory($value, string $message = ''): string
    {
        static::string($value);
        if (!\is_dir($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not a directory.', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function readable($value, string $message = ''): string
    {
        static::string($value);
        if (!\is_readable($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not readable.', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function writable($value, string $message = ''): string
    {
        static::string($value);
        if (!\is_writable($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not writable.', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-assert class-string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function classExists($value, string $message = ''): string
    {
        static::string($value);
        if (!\class_exists($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an existing class name. Got: %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     *
     * @psalm-assert class-string<ExpectedType> $value
     *
     * @param mixed $class
     *
     * @return class-string<ExpectedType>
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function subclassOf($value, $class, string $message = ''): string
    {
        static::string($value);
        static::string($class);
        if (!\is_subclass_of($value, $class)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected a sub-class of %2$s. Got: %s', static::valueToString($value), static::valueToString($class)));
        }
        return $value;
    }
    /**
     * @psalm-assert class-string $value
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function interfaceExists($value, string $message = ''): string
    {
        static::string($value);
        if (!\interface_exists($value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an existing interface name. got %s', static::valueToString($value)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     *
     * @psalm-assert class-string<ExpectedType>|ExpectedType $value
     *
     * @param mixed $value
     * @param mixed $interface
     *
     * @throws InvalidArgumentException
     * @return object|string
     */
    public static function implementsInterface($value, $interface, string $message = '')
    {
        static::objectish($value);
        if (!\in_array($interface, \class_implements($value), \true)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an implementation of %2$s. Got: %s', static::valueToString($value), static::valueToString($interface)));
        }
        return $value;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     *
     * @throws InvalidArgumentException
     * @return object|string
     * @param mixed $property
     */
    public static function propertyExists($classOrObject, $property, string $message = '')
    {
        static::objectish($classOrObject);
        if (!\property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the property %s to exist.', static::valueToString($property)));
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @psalm-param class-string|object $classOrObject
     *
     * @throws InvalidArgumentException
     * @param mixed $property
     * @return mixed
     */
    public static function propertyNotExists($classOrObject, $property, string $message = '')
    {
        if (!(\is_string($classOrObject) || \is_object($classOrObject)) || \property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the property %s to not exist.', static::valueToString($property)));
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @psalm-param class-string|object $classOrObject
     *
     * @throws InvalidArgumentException
     * @return object|string
     * @param mixed $method
     */
    public static function methodExists($classOrObject, $method, string $message = '')
    {
        static::objectish($classOrObject);
        if (!\method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the method %s to exist.', static::valueToString($method)));
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param mixed $classOrObject
     * @psalm-param class-string|object $classOrObject
     *
     * @throws InvalidArgumentException
     * @param mixed $method
     * @return mixed
     */
    public static function methodNotExists($classOrObject, $method, string $message = '')
    {
        static::objectish($classOrObject);
        if (\method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the method %s to not exist.', static::valueToString($method)));
        }
        return $classOrObject;
    }
    /**
     * @psalm-pure
     *
     * @param string|int $key
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function keyExists($array, $key, string $message = ''): array
    {
        static::isArray($array, $message);
        if (!(isset($array[$key]) || \array_key_exists($key, $array))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the key %s to exist.', static::valueToString($key)));
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @param string|int $key
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function keyNotExists($array, $key, string $message = ''): array
    {
        static::isArray($array, $message);
        if (isset($array[$key]) || \array_key_exists($key, $array)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected the key %s to not exist.', static::valueToString($key)));
        }
        return $array;
    }
    /**
     * Checks if a value is a valid array key (int or string).
     *
     * @psalm-pure
     *
     * @psalm-assert array-key $value
     *
     * @throws InvalidArgumentException
     * @return int|string
     * @param mixed $value
     */
    public static function validArrayKey($value, string $message = '')
    {
        if (!(\is_int($value) || \is_string($value))) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected string or integer. Got: %s', static::typeToString($value)));
        }
        return $value;
    }
    /**
     * @throws InvalidArgumentException
     * @return mixed[]|\Countable
     * @param mixed $array
     * @param mixed $number
     */
    public static function count($array, $number, string $message = '')
    {
        static::isCountable($array);
        static::integerish($number);
        static::eq(\count($array), $number, \sprintf($message ?: 'Expected an array to contain %d elements. Got: %d.', $number, \count($array)));
        return $array;
    }
    /**
     * @throws InvalidArgumentException
     * @return mixed[]|\Countable
     * @param mixed $array
     * @param mixed $min
     */
    public static function minCount($array, $min, string $message = '')
    {
        static::isCountable($array);
        static::integerish($min);
        if (\count($array) < $min) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain at least %2$d elements. Got: %d', \count($array), $min));
        }
        return $array;
    }
    /**
     * @throws InvalidArgumentException
     * @return mixed[]|\Countable
     * @param mixed $array
     * @param mixed $max
     */
    public static function maxCount($array, $max, string $message = '')
    {
        static::isCountable($array);
        static::integerish($max);
        if (\count($array) > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain at most %2$d elements. Got: %d', \count($array), $max));
        }
        return $array;
    }
    /**
     * @throws InvalidArgumentException
     * @return mixed[]|\Countable
     * @param mixed $array
     * @param mixed $min
     * @param mixed $max
     */
    public static function countBetween($array, $min, $max, string $message = '')
    {
        static::isCountable($array);
        static::integerish($min);
        static::integerish($max);
        $count = \count($array);
        if ($count < $min || $count > $max) {
            static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain between %2$d and %3$d elements. Got: %d', $count, $min, $max));
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert list $array
     *
     * @psalm-return list
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function isList($array, string $message = ''): array
    {
        $arrayIsListFunction = function (array $array): bool {
            if (function_exists('array_is_list')) {
                return array_is_list($array);
            }
            if ($array === []) {
                return \true;
            }
            $current_key = 0;
            foreach ($array as $key => $noop) {
                if ($key !== $current_key) {
                    return \false;
                }
                ++$current_key;
            }
            return \true;
        };
        if (!\is_array($array) || !$arrayIsListFunction($array)) {
            static::reportInvalidArgument($message ?: 'Expected list - non-associative array.');
        }
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @psalm-assert non-empty-list $array
     *
     * @psalm-return non-empty-list
     *
     * @throws InvalidArgumentException
     * @param mixed $array
     */
    public static function isNonEmptyList($array, string $message = ''): array
    {
        static::isList($array, $message);
        static::notEmpty($array, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @template T
     *
     * @psalm-assert array<string, T> $array
     *
     * @param mixed $array
     *
     * @return array<string, T>
     *
     * @throws InvalidArgumentException
     */
    public static function isMap($array, string $message = ''): array
    {
        static::isArray($array, $message);
        $arrayIsListFunction = function (array $array): bool {
            if (function_exists('array_is_list')) {
                return array_is_list($array);
            }
            if ($array === []) {
                return \true;
            }
            $current_key = 0;
            foreach ($array as $key => $noop) {
                if ($key !== $current_key) {
                    return \false;
                }
                ++$current_key;
            }
            return \true;
        };
        if (\count($array) > 0 && $arrayIsListFunction($array)) {
            static::reportInvalidArgument($message ?: 'Expected map - associative array with string keys.');
        }
        return $array;
    }
    /**
     * @psalm-assert callable $callable
     *
     * @param mixed $callable
     *
     * @return Closure|callable-string
     *
     * @throws InvalidArgumentException
     */
    public static function isStatic($callable, string $message = '')
    {
        static::isCallable($callable, $message);
        $callable = static::callableToClosure($callable);
        $reflection = new ReflectionFunction($callable);
        if (!$reflection->isStatic()) {
            static::reportInvalidArgument($message ?: 'Closure is not static.');
        }
        return $callable;
    }
    /**
     * @psalm-assert callable $callable
     *
     * @param mixed $callable
     *
     * @return Closure|callable-string
     *
     * @throws InvalidArgumentException
     */
    public static function notStatic($callable, string $message = '')
    {
        static::isCallable($callable, $message);
        $callable = static::callableToClosure($callable);
        $reflection = new ReflectionFunction($callable);
        if ($reflection->isStatic()) {
            static::reportInvalidArgument($message ?: 'Closure is not static.');
        }
        return $callable;
    }
    /**
     * @psalm-pure
     *
     * @template T
     *
     * @psalm-assert array<string, T> $array
     * @psalm-assert !empty $array
     *
     * @param mixed $array
     *
     * @return array<string, T>
     *
     * @throws InvalidArgumentException
     */
    public static function isNonEmptyMap($array, string $message = ''): array
    {
        static::isMap($array, $message);
        static::notEmpty($array, $message);
        return $array;
    }
    /**
     * @psalm-pure
     *
     * @throws InvalidArgumentException
     * @param mixed $value
     */
    public static function uuid($value, string $message = ''): string
    {
        static::string($value, $message);
        $originalValue = $value;
        $value = \str_replace(['urn:', 'uuid:', '{', '}'], '', $value);
        // The nil UUID is special form of UUID that is specified to have all
        // 128 bits set to zero.
        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return $originalValue;
        }
        if (!\preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/D', $value)) {
            static::reportInvalidArgument(\sprintf($message ?: 'Value %s is not a valid UUID.', static::valueToString($value)));
        }
        return $originalValue;
    }
    /**
     * @psalm-param class-string<Throwable> $class
     *
     * @throws InvalidArgumentException
     * @param mixed $expression
     */
    public static function throws($expression, string $class = Throwable::class, string $message = ''): callable
    {
        static::string($class);
        static::isCallable($expression);
        $actual = 'none';
        try {
            $expression();
        } catch (Throwable $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return $expression;
            }
        }
        static::reportInvalidArgument($message ?: \sprintf('Expected to throw "%s", got "%s"', $class, $actual));
    }
    /**
     * @psalm-pure
     *
     * @return Closure|callable-string
     */
    protected static function callableToClosure(callable $callable)
    {
        if (\is_string($callable) && \function_exists($callable)) {
            return $callable;
        }
        if ($callable instanceof Closure) {
            return $callable;
        }
        return \Closure::fromCallable($callable);
    }
    /**
     * @psalm-pure
     * @param mixed $value
     */
    protected static function valueToString($value): string
    {
        if (null === $value) {
            return 'null';
        }
        if (\true === $value) {
            return 'true';
        }
        if (\false === $value) {
            return 'false';
        }
        if (\is_array($value)) {
            return 'array';
        }
        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value) . ': ' . self::valueToString($value->__toString());
            }
            if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
                return \get_class($value) . ': ' . self::valueToString($value->format('c'));
            }
            if (class_exists(\get_class($value))) {
                return \get_class($value) . '::' . $value->name;
            }
            return \get_class($value);
        }
        if (\is_resource($value)) {
            return 'resource';
        }
        if (\is_string($value)) {
            return '"' . $value . '"';
        }
        return (string) $value;
    }
    /**
     * @psalm-pure
     * @param mixed $value
     */
    protected static function typeToString($value): string
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }
    protected static function strlen(string $value): int
    {
        if (!\function_exists('mb_detect_encoding') && !\function_exists('RectorPrefix202601\mb_detect_encoding')) {
            return \strlen($value);
        }
        if (\false === $encoding = \mb_detect_encoding($value)) {
            return \strlen($value);
        }
        return \mb_strlen($value, $encoding);
    }
    /**
     * @psalm-pure this method is not supposed to perform side effects
     *
     * @throws InvalidArgumentException
     * @return never
     */
    protected static function reportInvalidArgument(string $message)
    {
        throw new InvalidArgumentException($message);
    }
    private function __construct()
    {
    }
}

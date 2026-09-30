<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Validation;

use InvalidArgumentException;
use ReflectionClass;
/**
 * Minimal in-house assertions, so the package carries no runtime dependency.
 *
 * @see \Entropy\Tests\Validation\AssertTest
 */
final class Assert
{
    /**
     * @param mixed $value
     * @phpstan-assert string $value
     */
    public static function string($value, string $message = ''): void
    {
        if (!is_string($value)) {
            self::fail($message !== '' ? $message : 'Expected a string.');
        }
    }
    /**
     * @param mixed $value
     * @phpstan-assert mixed[] $value
     */
    public static function isArray($value, string $message = ''): void
    {
        if (!is_array($value)) {
            self::fail($message !== '' ? $message : 'Expected an array.');
        }
    }
    /**
     * @param mixed $value
     * @phpstan-assert !false $value
     */
    public static function notFalse($value, string $message = ''): void
    {
        if ($value === \false) {
            self::fail($message !== '' ? $message : 'Expected a value other than false.');
        }
    }
    public static function fileExists(string $path, string $message = ''): void
    {
        if (!file_exists($path)) {
            self::fail($message !== '' ? $message : sprintf('The file "%s" does not exist.', $path));
        }
    }
    public static function directory(string $path, string $message = ''): void
    {
        if (!is_dir($path)) {
            self::fail($message !== '' ? $message : sprintf('The path "%s" is not a directory.', $path));
        }
    }
    /**
     * @param object|class-string $classOrObject
     */
    public static function methodExists($classOrObject, string $method, string $message = ''): void
    {
        $reflectionClass = new ReflectionClass($classOrObject);
        if (!$reflectionClass->hasMethod($method)) {
            self::fail($message !== '' ? $message : sprintf('The method "%s" does not exist.', $method));
        }
    }
    /**
     * @param mixed $values
     */
    public static function allString($values, string $message = ''): void
    {
        self::isIterable($values);
        foreach ($values as $value) {
            self::string($value, $message);
        }
    }
    /**
     * @param mixed $values
     */
    public static function allIsInstanceOf($values, string $class, string $message = ''): void
    {
        self::isIterable($values);
        foreach ($values as $value) {
            if (!$value instanceof $class) {
                self::fail($message !== '' ? $message : sprintf('Expected an instance of "%s" in every item.', $class));
            }
        }
    }
    /**
     * @param mixed $values
     * @phpstan-assert iterable<mixed> $values
     */
    private static function isIterable($values): void
    {
        if (!is_iterable($values)) {
            self::fail('Expected an iterable value.');
        }
    }
    private static function fail(string $message): void
    {
        throw new InvalidArgumentException($message);
    }
}

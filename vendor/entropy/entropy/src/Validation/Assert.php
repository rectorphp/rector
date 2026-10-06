<?php

declare (strict_types=1);
namespace RectorPrefix202610\Entropy\Validation;

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
     * @api
     * @param mixed $value
     */
    public static function notEmpty($value, string $message = ''): void
    {
        if (!$value) {
            self::fail($message !== '' ? $message : 'Expected a non-empty value.');
        }
    }
    /**
     * @api
     * @param mixed $value
     * @param class-string $class
     * @phpstan-assert object $value
     */
    public static function isInstanceOf($value, string $class, string $message = ''): void
    {
        if (!$value instanceof $class) {
            self::fail($message !== '' ? $message : sprintf('Expected an instance of "%s".', $class));
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
     * @param iterable<mixed> $values
     * @phpstan-assert iterable<string> $values
     */
    public static function allString(iterable $values, string $message = ''): void
    {
        foreach ($values as $value) {
            self::string($value, $message);
        }
    }
    /**
     * @api
     * @param iterable<string> $values
     */
    public static function allFileExists(iterable $values, string $message = ''): void
    {
        foreach ($values as $value) {
            self::fileExists($value, $message);
        }
    }
    /**
     * @api
     * @param iterable<string> $values
     */
    public static function allDirectory(iterable $values, string $message = ''): void
    {
        foreach ($values as $value) {
            self::directory($value, $message);
        }
    }
    /**
     * @param iterable<mixed> $values
     * @param class-string $class
     */
    public static function allIsInstanceOf(iterable $values, string $class, string $message = ''): void
    {
        foreach ($values as $value) {
            if (!$value instanceof $class) {
                self::fail($message !== '' ? $message : sprintf('Expected an instance of "%s" in every item.', $class));
            }
        }
    }
    /**
     * @api
     * @param iterable<mixed> $values
     * @param class-string $class
     */
    public static function allIsAOf(iterable $values, string $class, string $message = ''): void
    {
        foreach ($values as $value) {
            if (!is_a($value, $class, \true)) {
                self::fail($message !== '' ? $message : sprintf('Expected a class-string of type "%s" in every item.', $class));
            }
        }
    }
    private static function fail(string $message): void
    {
        throw new InvalidArgumentException($message);
    }
}

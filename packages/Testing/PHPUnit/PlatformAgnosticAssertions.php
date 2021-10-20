<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use RectorPrefix20211020\Nette\Utils\FileSystem;
use PHPUnit\Framework\Constraint\IsEqual;
/**
 * Relaxes phpunit assertions to be forgiving about platform issues, like directory-separators or newlines.
 * Mostly required to make assertion work on Windows.
 *
 * @note Cannot be used, as it breaks compatibility with PHPUnit 8 and 9 @see https://github.com/rectorphp/rector/issues/6709
 */
trait PlatformAgnosticAssertions
{
    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @psalm-template ExpectedType
     * @psalm-param ExpectedType $expected
     * @psalm-assert =ExpectedType $actual
     * @param string $message
     */
    public static function assertSame($expected, $actual, $message = '') : void
    {
        if (\is_string($expected)) {
            $expected = self::normalize($expected);
        }
        if (\is_string($actual)) {
            $actual = self::normalize($actual);
        }
        parent::assertSame($expected, $actual, $message);
    }
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file.
     * @param string $expectedFile
     * @param string $actualString
     * @param string $message
     */
    public static function assertStringEqualsFile($expectedFile, $actualString, $message = '') : void
    {
        parent::assertFileExists($expectedFile, $message);
        $expectedString = self::getNormalizedFileContents($expectedFile);
        $isEqual = new \PHPUnit\Framework\Constraint\IsEqual($expectedString);
        $actualString = self::normalize($actualString);
        parent::assertThat($actualString, $isEqual, $message);
    }
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file.
     * @param string $expected
     * @param string $actual
     * @param string $message
     */
    public static function assertFileEquals($expected, $actual, $message = '') : void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);
        $isEqual = new \PHPUnit\Framework\Constraint\IsEqual(self::getNormalizedFileContents($expected));
        static::assertThat(self::getNormalizedFileContents($actual), $isEqual, $message);
    }
    /**
     * @return mixed[]|string
     */
    private static function normalize(string $string)
    {
        $string = \str_replace("\r\n", "\n", $string);
        return \str_replace(\DIRECTORY_SEPARATOR, '/', $string);
    }
    private static function getNormalizedFileContents(string $filePath) : string
    {
        $expectedString = \RectorPrefix20211020\Nette\Utils\FileSystem::read($filePath);
        return self::normalize($expectedString);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Bootstrap;

/**
 * Reads an option value from raw argv, used during bootstrap before the console exists.
 *
 * @see \Rector\Tests\Bootstrap\CliOptionValueResolverTest
 */
final class CliOptionValueResolver
{
    /**
     * @var string
     */
    private const END_OF_OPTIONS = '--';
    /**
     * Resolve the value of "--name value", "--name=value", "-c value" or "-c=value" from raw argv.
     *
     * @param array<int, mixed> $argv
     * @param string[] $optionNames fully dashed, e.g. ['--config', '-c']
     */
    public static function resolve(array $argv, array $optionNames): ?string
    {
        foreach ($argv as $index => $arg) {
            if (!is_string($arg)) {
                continue;
            }
            // everything after "--" is a positional argument
            if ($arg === self::END_OF_OPTIONS) {
                return null;
            }
            foreach ($optionNames as $optionName) {
                if ($arg === $optionName) {
                    $nextArg = $argv[$index + 1] ?? null;
                    return is_string($nextArg) ? $nextArg : null;
                }
                if (strncmp($arg, $optionName . '=', strlen($optionName . '=')) === 0) {
                    return (string) substr($arg, strlen($optionName) + 1);
                }
            }
        }
        return null;
    }
}

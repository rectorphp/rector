<?php

declare (strict_types=1);
namespace Rector\Console;

use Rector\Exception\Configuration\InvalidConfigurationException;
use RectorPrefix202608\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202608\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202608\Symfony\Component\Console\Style\SymfonyStyle;
final class Notifier
{
    public static function notifyNotSuitableMethodForPHP74(string $calledMethod): void
    {
        if (\PHP_VERSION_ID >= 80000) {
            return;
        }
        $message = sprintf('The "%s()" method uses named arguments. Its suitable for PHP 8.0+. In lower PHP versions, use "withSets([...])" method instead', $calledMethod);
        $symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $symfonyStyle->warning($message);
        sleep(3);
    }
    public static function notifyDeprecatedPhpSet(string $set): void
    {
        $message = sprintf('The per-version PHP set "%s" is deprecated. Use "withPhpSets()" or "withPhpLevel()" instead, ' . 'they pick the rules by your PHP version automatically.', $set);
        $symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $symfonyStyle->warning($message);
    }
    public static function errorWithPhpSetsNotSuitableForPHP74AndLower(): void
    {
        if (\PHP_VERSION_ID >= 80000) {
            return;
        }
        throw new InvalidConfigurationException('The "->withPhpSets()" method uses named arguments. Its suitable for PHP 8.0+. Use "->withPhpLevel()" in lower PHP versions instead.');
    }
}

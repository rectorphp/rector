<?php

declare (strict_types=1);
namespace Rector\Console;

use RectorPrefix202406\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202406\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202406\Symfony\Component\Console\Style\SymfonyStyle;
final class Notifier
{
    public static function notifyNotSuitableMethodForPHP74(string $calledMethod) : void
    {
        if (\PHP_VERSION_ID >= 80000) {
            return;
        }
        $message = \sprintf('The "%s()" method uses named arguments. Its suitable for PHP 8.0+. In lower PHP versions, use "withSets([...])" method instead', $calledMethod);
        $symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $symfonyStyle->warning($message);
        \sleep(3);
    }
    public static function notifyNotSuitableMethodForPHP80(string $calledMethod) : void
    {
        // current project version check
        if (\PHP_VERSION_ID < 80000) {
            return;
        }
        $message = \sprintf('The "%s()" method is suitable for PHP 7.4 and lower. Use the following methods instead:

    - "withPhpSets()" in PHP 8.0+
    - "withSets([...])" for use in both php ^7.2 and php 8.0+.', $calledMethod);
        $symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $symfonyStyle->warning($message);
        \sleep(3);
    }
    public static function notifyWithPhpSetsNotSuitableForPHP80() : void
    {
        if (\PHP_VERSION_ID >= 80000) {
            return;
        }
        $message = 'The "withPhpSets()" method uses named arguments. Its suitable for PHP 8.0+. Use the following methods instead:

    - "withPhp53Sets()" ... "withPhp74Sets()" in lower PHP versions
    - "withSets([...])" for use both PHP ^7.2 and php 8.0+.';
        $symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $symfonyStyle->warning($message);
        \sleep(3);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Console;

use RectorPrefix202411\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202411\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202411\Symfony\Component\Console\Style\SymfonyStyle;
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
    public static function notifyWithPhpSetsNotSuitableForPHP80() : void
    {
        if (\PHP_VERSION_ID >= 80000) {
            return;
        }
        $message = 'The "withPhpSets()" method uses named arguments. Its suitable for PHP 8.0+. Use more explicit "withPhp53Sets()" ... "withPhp74Sets()" in lower PHP versions instead.';
        $symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $symfonyStyle->warning($message);
        \sleep(3);
    }
}

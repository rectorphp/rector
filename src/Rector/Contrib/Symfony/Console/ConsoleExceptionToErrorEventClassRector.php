<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Console;

use Rector\Rector\AbstractClassReplacerRector;

/**
 * Ref:
 * - https://github.com/symfony/symfony/pull/22441/files
 * - https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#console
 *
 * Before:
 * - Symfony\Component\Console\Event\ConsoleExceptionEvent
 *
 * After:
 * - Symfony\Component\Console\Event\ConsoleErrorEvent
 */
final class ConsoleExceptionToErrorEventClassRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Symfony\Component\Console\Event\ConsoleExceptionEvent' => 'Symfony\Component\Console\Event\ConsoleErrorEvent',
        ];
    }
}

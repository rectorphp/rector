<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Debug;

use Rector\Rector\AbstractClassReplacerRector;

/**
 *
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#debug
 *
 * Before:
 * - Symfony\Component\Debug\Exception\ContextErrorException
 *
 * After:
 * - \ErrorException
 */
final class ContextErrorExceptionToErrorExceptionRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Symfony\Component\Debug\Exception\ContextErrorException' => 'ErrorException'
        ];
    }
}

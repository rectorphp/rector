<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use Rector\Rector\AbstractClassReplacerRector;

/**
 * Covers https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-6.0.md#changed-1
 */
final class NamespaceClassRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'PHPUnit_Framework_TestCase' => 'PHPUnit\Framework\TestCase',
        ];
    }
}

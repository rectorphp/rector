<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use Rector\Rector\AbstractClassReplacerRector;
use Rector\Rector\Set\SetNames;

final class NamespaceClassRector extends AbstractClassReplacerRector
{
    public function getSetName(): string
    {
        return SetNames::PHPUNIT;
    }

    public function sinceVersion(): float
    {
        return 6.0;
    }

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

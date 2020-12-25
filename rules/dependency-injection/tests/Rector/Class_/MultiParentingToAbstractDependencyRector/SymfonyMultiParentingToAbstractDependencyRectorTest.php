<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector;

use Iterator;
use Rector\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SymfonyMultiParentingToAbstractDependencyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSymfony');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MultiParentingToAbstractDependencyRector::class => [
                MultiParentingToAbstractDependencyRector::FRAMEWORK => 'symfony',
            ],
        ];
    }
}

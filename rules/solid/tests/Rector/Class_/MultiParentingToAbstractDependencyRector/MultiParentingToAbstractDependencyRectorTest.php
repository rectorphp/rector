<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\Class_\MultiParentingToAbstractDependencyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MultiParentingToAbstractDependencyRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MultiParentingToAbstractDependencyRector::class => [
                MultiParentingToAbstractDependencyRector::FRAMEWORK => 'nette',
            ],
        ];
    }
}

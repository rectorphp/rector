<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SOLID\Rector\Class_\MultiParentingToAbstractDependencyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SymfonyMultiParentingToAbstractDependencyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSymfony');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            MultiParentingToAbstractDependencyRector::class => [
                '$framework' => 'symfony',
            ],
        ];
    }
}

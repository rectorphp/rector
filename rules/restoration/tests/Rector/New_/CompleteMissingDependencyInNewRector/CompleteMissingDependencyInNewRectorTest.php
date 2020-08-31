<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Restoration\Rector\New_\CompleteMissingDependencyInNewRector;
use Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Source\RandomDependency;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CompleteMissingDependencyInNewRectorTest extends AbstractRectorTestCase
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
     * @return array<string, array<string, array<string, string>>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            CompleteMissingDependencyInNewRector::class => [
                CompleteMissingDependencyInNewRector::CLASS_TO_INSTANTIATE_BY_TYPE => [
                    RandomDependency::class => RandomDependency::class,
                ],
            ],
        ];
    }
}

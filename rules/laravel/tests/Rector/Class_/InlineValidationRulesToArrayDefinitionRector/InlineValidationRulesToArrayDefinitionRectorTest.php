<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Laravel\Rector\Class_\InlineValidationRulesToArrayDefinitionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InlineValidationRulesToArrayDefinitionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return InlineValidationRulesToArrayDefinitionRector::class;
    }
}

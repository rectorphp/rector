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
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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

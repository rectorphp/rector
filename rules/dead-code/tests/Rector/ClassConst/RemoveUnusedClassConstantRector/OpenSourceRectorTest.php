<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OpenSourceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->setParameter(Option::PROJECT_TYPE, Option::PROJECT_TYPE_OPEN_SOURCE);
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureOpenSource');
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedClassConstantRector::class;
    }
}

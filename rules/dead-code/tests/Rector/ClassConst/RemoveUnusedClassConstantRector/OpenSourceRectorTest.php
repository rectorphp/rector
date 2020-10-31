<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\ProjectType;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OpenSourceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->setParameter(Option::PROJECT_TYPE, ProjectType::OPEN_SOURCE);
        $this->doTestFileInfo($fileInfo);
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

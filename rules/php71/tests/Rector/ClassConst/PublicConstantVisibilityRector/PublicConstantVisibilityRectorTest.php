<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\ClassConst\PublicConstantVisibilityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PublicConstantVisibilityRectorTest extends AbstractRectorTestCase
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
        return PublicConstantVisibilityRector::class;
    }
}

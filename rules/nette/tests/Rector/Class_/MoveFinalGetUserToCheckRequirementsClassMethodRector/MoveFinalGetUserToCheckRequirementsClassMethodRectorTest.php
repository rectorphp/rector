<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector;

use Iterator;
use Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveFinalGetUserToCheckRequirementsClassMethodRectorTest extends AbstractRectorTestCase
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
        return MoveFinalGetUserToCheckRequirementsClassMethodRector::class;
    }
}

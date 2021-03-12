<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Property\AddUuidAnnotationsToIdPropertyRector;

use Iterator;
use Rector\Doctrine\Rector\Property\AddUuidAnnotationsToIdPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddUuidAnnotationsToIdPropertyRectorTest extends AbstractRectorTestCase
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
        return AddUuidAnnotationsToIdPropertyRector::class;
    }
}

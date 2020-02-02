<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector;

use Iterator;
use Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddUuidMirrorForRelationPropertyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return AddUuidMirrorForRelationPropertyRector::class;
    }
}

<?php

declare(strict_types=1);

namespace Rector\Oxid\Tests\Rector\FuncCall\OxidReplaceBackwardsCompatabilityClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Oxid\Rector\FuncCall\OxidReplaceBackwardsCompatabilityClassRector;

final class OxidReplaceBackwardsCompatabilityClassRectorTest extends AbstractRectorTestCase
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
        return OxidReplaceBackwardsCompatabilityClassRector::class;
    }
}

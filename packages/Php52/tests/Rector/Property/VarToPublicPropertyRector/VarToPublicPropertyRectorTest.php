<?php

declare(strict_types=1);

namespace Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;

final class VarToPublicPropertyRectorTest extends AbstractRectorTestCase
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
        return VarToPublicPropertyRector::class;
    }
}

<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;

final class PrivatizeLocalGetterToPropertyRectorTest extends AbstractRectorTestCase
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
        return PrivatizeLocalGetterToPropertyRector::class;
    }
}

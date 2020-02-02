<?php

declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector;

use Iterator;
use Rector\Architecture\Rector\Class_\ConstructorInjectionToActionInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConstructorInjectionToActionInjectionRectorTest extends AbstractRectorTestCase
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
        return ConstructorInjectionToActionInjectionRector::class;
    }
}

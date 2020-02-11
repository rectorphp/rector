<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;

final class FacadeStaticCallToConstructorInjectionRectorTest extends AbstractRectorTestCase
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
        return FacadeStaticCallToConstructorInjectionRector::class;
    }
}

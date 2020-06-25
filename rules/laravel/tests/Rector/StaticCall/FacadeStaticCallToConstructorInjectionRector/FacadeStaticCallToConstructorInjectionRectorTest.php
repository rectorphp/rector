<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FacadeStaticCallToConstructorInjectionRectorTest extends AbstractRectorTestCase
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
        return FacadeStaticCallToConstructorInjectionRector::class;
    }
}

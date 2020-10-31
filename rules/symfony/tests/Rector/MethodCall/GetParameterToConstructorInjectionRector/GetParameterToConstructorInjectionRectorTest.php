<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector;

use Iterator;
use Rector\Symfony\Rector\MethodCall\GetParameterToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GetParameterToConstructorInjectionRectorTest extends AbstractRectorTestCase
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
        return GetParameterToConstructorInjectionRector::class;
    }
}

<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector;

use Iterator;
use Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Php74Test extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePhp74');
    }

    protected function getRectorClass(): string
    {
        return ContextGetByTypeToConstructorInjectionRector::class;
    }
}

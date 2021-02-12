<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Php80Test extends AbstractRectorTestCase
{
    /**
     * @requires PHP 8.0
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePhp80');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/property_promotion.php';
    }
}

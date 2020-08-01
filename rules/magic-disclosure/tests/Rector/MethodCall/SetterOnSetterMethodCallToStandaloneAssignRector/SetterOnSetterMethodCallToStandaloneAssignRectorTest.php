<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\SetterOnSetterMethodCallToStandaloneAssignRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\MethodCall\SetterOnSetterMethodCallToStandaloneAssignRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetterOnSetterMethodCallToStandaloneAssignRectorTest extends AbstractRectorTestCase
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
        return SetterOnSetterMethodCallToStandaloneAssignRector::class;
    }
}

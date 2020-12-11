<?php

declare(strict_types=1);

namespace Rector\Symfony4\Tests\Rector\MethodCall\FormIsValidRector;

use Iterator;
use Rector\Symfony4\Rector\MethodCall\FormIsValidRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormIsValidRectorTest extends AbstractRectorTestCase
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
        return FormIsValidRector::class;
    }
}

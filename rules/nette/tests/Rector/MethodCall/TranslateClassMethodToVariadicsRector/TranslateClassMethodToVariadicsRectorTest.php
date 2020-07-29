<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\TranslateClassMethodToVariadicsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Nette\Rector\MethodCall\TranslateClassMethodToVariadicsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TranslateClassMethodToVariadicsRectorTest extends AbstractRectorTestCase
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
        return TranslateClassMethodToVariadicsRector::class;
    }
}

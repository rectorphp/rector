<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\TranslateClassMethodToVariadicsRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class TranslateClassMethodToVariadicsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\Nette\Rector\MethodCall\TranslateClassMethodToVariadicsRector::class;
    }
}

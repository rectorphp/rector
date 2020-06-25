<?php

declare(strict_types=1);

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PregReplaceEModifierRectorTest extends AbstractRectorTestCase
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
        return PregReplaceEModifierRector::class;
    }
}

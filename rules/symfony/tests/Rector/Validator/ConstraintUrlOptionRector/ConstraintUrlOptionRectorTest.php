<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Validator\ConstraintUrlOptionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ConstraintUrlOptionRectorTest extends AbstractRectorTestCase
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
        return ConstraintUrlOptionRector::class;
    }
}

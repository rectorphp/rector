<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\FormIsValidRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Form\FormIsValidRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormIsValidRectorTest extends AbstractRectorTestCase
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
        return FormIsValidRector::class;
    }
}

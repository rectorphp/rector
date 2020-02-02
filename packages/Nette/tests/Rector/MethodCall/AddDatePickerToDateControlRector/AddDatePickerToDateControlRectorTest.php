<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector;

use Iterator;
use Rector\Nette\Rector\MethodCall\AddDatePickerToDateControlRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddDatePickerToDateControlRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return AddDatePickerToDateControlRector::class;
    }
}

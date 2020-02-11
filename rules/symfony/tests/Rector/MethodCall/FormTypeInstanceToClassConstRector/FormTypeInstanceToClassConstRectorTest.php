<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector;

final class FormTypeInstanceToClassConstRectorTest extends AbstractRectorTestCase
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
        return FormTypeInstanceToClassConstRector::class;
    }
}

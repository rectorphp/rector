<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector;

final class WrapTransParameterNameRectorTest extends AbstractRectorTestCase
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
        return WrapTransParameterNameRector::class;
    }
}

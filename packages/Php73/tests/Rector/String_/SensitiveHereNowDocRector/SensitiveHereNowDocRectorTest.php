<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\String_\SensitiveHereNowDocRector;

use Iterator;
use Rector\Php73\Rector\String_\SensitiveHereNowDocRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SensitiveHereNowDocRectorTest extends AbstractRectorTestCase
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
        return SensitiveHereNowDocRector::class;
    }
}

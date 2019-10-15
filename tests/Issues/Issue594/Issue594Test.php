<?php

declare(strict_types=1);

namespace Rector\Tests\Issues\Issue594;

use Iterator;
use Rector\Symfony\Rector\HttpKernel\GetRequestRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue594Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture594.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return GetRequestRector::class;
    }
}

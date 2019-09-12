<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector;

use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SpecificAssertContainsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SpecificAssertContainsRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector;

use Iterator;
use Rector\StrictCodeQuality\Rector\Stmt\VarInlineAnnotationToAssertRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class VarInlineAnnotationToAssertRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/scalar.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return VarInlineAnnotationToAssertRector::class;
    }
}

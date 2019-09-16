<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector;

use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ResponseStatusCodeRectorTest extends AbstractRectorTestCase
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
        return ResponseStatusCodeRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\BinaryOp\IsIterableRector;

use Rector\Php71\Rector\BinaryOp\IsIterableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\ValueObject\PhpVersionFeature;

final class PolyfillRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/polyfill_function.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return IsIterableRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::ITERABLE_TYPE;
    }
}

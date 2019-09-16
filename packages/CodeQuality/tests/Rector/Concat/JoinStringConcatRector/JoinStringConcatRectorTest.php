<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector;

use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JoinStringConcatRectorTest extends AbstractRectorTestCase
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
        return JoinStringConcatRector::class;
    }
}

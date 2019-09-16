<?php declare(strict_types=1);

namespace Rector\Silverstripe\Tests\Rector\ConstantToStaticCallRector;

use Rector\Silverstripe\Rector\ConstantToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConstantToStaticCallRectorTest extends AbstractRectorTestCase
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
        return ConstantToStaticCallRector::class;
    }
}

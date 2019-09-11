<?php declare(strict_types=1);

namespace Rector\Silverstripe\Tests\Rector\DefineConstantToStaticCallRector;

use Rector\Silverstripe\Rector\DefineConstantToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DefineConstantToStaticCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return DefineConstantToStaticCallRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}

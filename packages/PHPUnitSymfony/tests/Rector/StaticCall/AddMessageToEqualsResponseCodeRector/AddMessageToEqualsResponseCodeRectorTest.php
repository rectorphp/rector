<?php declare(strict_types=1);

namespace Rector\PHPUnitSymfony\Tests\Rector\StaticCall\AddMessageToEqualsResponseCodeRector;

use Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddMessageToEqualsResponseCodeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/method_call.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddMessageToEqualsResponseCodeRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RemoveReferenceFromCallRector;

use Rector\Php\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveReferenceFromCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFileWithoutAutoload($file);
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
        return RemoveReferenceFromCallRector::class;
    }
}

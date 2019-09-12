<?php declare(strict_types=1);

namespace Rector\_Package_\Tests\Rector\_Category_\_Name_;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class _Name_Test extends AbstractRectorTestCase
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
        return \Rector\_Package_\Rector\_Category_\_Name_::class;
    }
}

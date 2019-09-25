<?php declare(strict_types=1);

namespace Rector\Php53\Tests\Rector\New_\NewReferenceToNewRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NewReferenceToNewRectorTest extends AbstractRectorTestCase
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
        return \Rector\Php53\Rector\New_\NewReferenceToNewRector::class;
    }
}

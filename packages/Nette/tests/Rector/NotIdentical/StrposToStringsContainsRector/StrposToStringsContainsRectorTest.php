<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector;

use Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StrposToStringsContainsRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/keep.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return StrposToStringsContainsRector::class;
    }
}

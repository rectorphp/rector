<?php declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector;

use Rector\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MissingClassConstantReferenceToStringRectorTest extends AbstractRectorTestCase
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
        return MissingClassConstantReferenceToStringRector::class;
    }
}

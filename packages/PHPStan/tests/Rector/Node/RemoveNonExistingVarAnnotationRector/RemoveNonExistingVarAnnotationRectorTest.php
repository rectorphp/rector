<?php declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector;

use Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveNonExistingVarAnnotationRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/subcontent.php.inc'];
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/if_case.php.inc'];
        yield [__DIR__ . '/Fixture/keep.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveNonExistingVarAnnotationRector::class;
    }
}

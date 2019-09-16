<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;

use Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertEqualsParameterToSpecificMethodsTypeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/remove_max_depth.php.inc'];
        yield [__DIR__ . '/Fixture/refactor_canonize.php.inc'];
        yield [__DIR__ . '/Fixture/refactor_delta.php.inc'];
        yield [__DIR__ . '/Fixture/refactor_ignore_case.php.inc'];
        yield [__DIR__ . '/Fixture/combination.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AssertEqualsParameterToSpecificMethodsTypeRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

final class AddClosureReturnTypeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/return_type_object.php.inc'];
        yield [__DIR__ . '/Fixture/callable_false_positive.php.inc'];
        yield [__DIR__ . '/Fixture/subtype_of_object.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddClosureReturnTypeRector::class;
    }
}

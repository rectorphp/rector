<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;

use Iterator;
use Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddDoesNotPerformAssertionToNonAssertingTestRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/test_in_annotation.php.inc'];
        yield [__DIR__ . '/Fixture/keep_assert.php.inc'];
        yield [__DIR__ . '/Fixture/keep_expected_exception.php.inc'];
        yield [__DIR__ . '/Fixture/keep_assert_in_call.php.inc'];
        yield [__DIR__ . '/Fixture/keep_assert_in_static_call.php.inc'];
        yield [__DIR__ . '/Fixture/keep_non_public.php.inc'];
        yield [__DIR__ . '/Fixture/keep_non_test.php.inc'];
        yield [__DIR__ . '/Fixture/keep_with_parent_method_assert.php.inc'];
        yield [__DIR__ . '/Fixture/keep_with_parent_method_static_assert.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddDoesNotPerformAssertionToNonAssertingTestRector::class;
    }
}

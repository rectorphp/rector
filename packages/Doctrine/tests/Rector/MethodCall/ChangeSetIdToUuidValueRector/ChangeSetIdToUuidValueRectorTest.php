<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector;

use Iterator;
use Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeSetIdToUuidValueRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/no_set_uuid.php.inc'];
        yield [__DIR__ . '/Fixture/other_direction.php.inc'];
        yield [__DIR__ . '/Fixture/with_constant.php.inc'];
        yield [__DIR__ . '/Fixture/with_int_constant_only.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ChangeSetIdToUuidValueRector::class;
    }
}

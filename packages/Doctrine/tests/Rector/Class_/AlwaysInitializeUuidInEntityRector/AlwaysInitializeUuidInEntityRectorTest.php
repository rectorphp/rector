<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector;

use Iterator;
use Rector\Doctrine\Rector\Class_\AlwaysInitializeUuidInEntityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AlwaysInitializeUuidInEntityRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/add_uuid_init.php.inc'];
        yield [__DIR__ . '/Fixture/add_uuid_init_to_construct.php.inc'];
        yield [__DIR__ . '/Fixture/skip_already_added.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AlwaysInitializeUuidInEntityRector::class;
    }
}

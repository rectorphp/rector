<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector;

use Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddUuidToEntityWhereMissingRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/already_has_constructor.php.inc'];
        yield [__DIR__ . '/Fixture/process_string_id.php.inc'];
        yield [__DIR__ . '/Fixture/with_parent_constructor.php.inc'];
        yield [__DIR__ . '/Fixture/add_single_table_inheritance.php.inc'];
        yield [__DIR__ . '/Fixture/add_single_table_inheritance_with_identifier.php.inc'];
        // skip
        yield [__DIR__ . '/Fixture/skip_id_with_uuid_type.php.inc'];
        yield [__DIR__ . '/Fixture/skip_id_with_uuid_binary_type.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AddUuidToEntityWhereMissingRector::class;
    }
}

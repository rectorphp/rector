<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector;

use Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddUuidToEntityWhereMissingRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/already_has_constructor.php.inc',
            __DIR__ . '/Fixture/process_string_id.php.inc',
            __DIR__ . '/Fixture/with_parent_constructor.php.inc',
            __DIR__ . '/Fixture/add_single_table_inheritance.php.inc',
            __DIR__ . '/Fixture/add_single_table_inheritance_with_identifier.php.inc',
            // skip
            __DIR__ . '/Fixture/skip_id_with_uuid_type.php.inc',
            __DIR__ . '/Fixture/skip_id_with_uuid_binary_type.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddUuidToEntityWhereMissingRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector;

use Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ImportFullyQualifiedNamesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/double_import.php.inc',
            __DIR__ . '/Fixture/double_import_with_existing.php.inc',
            __DIR__ . '/Fixture/already_with_use.php.inc',
            // keep
            __DIR__ . '/Fixture/keep.php.inc',
            __DIR__ . '/Fixture/keep_same_end.php.inc',
            __DIR__ . '/Fixture/keep_trait_use.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ImportFullyQualifiedNamesRector::class;
    }
}
